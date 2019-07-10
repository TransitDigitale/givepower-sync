<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

use App\Spectra;
use App\Device;
use DateTime;

class SpectraController extends DeviceController
{
    protected $datavizURI = '/dataviz.php';
    protected $downloadSensorRawURI = '/download.php?file=sensor_raw';
    protected $downloadSensorUnitURI = '/download.php?file=sensor_engunit';


    public function readData(Request $request, $deviceName)
    {
        $device = Device::where('name', $deviceName)->first();

        // We need to load the 2 Spectra Connect files, RAW + ENGUNIT
        // we have to change the tank levels value from psi (engunit) to percentage (raw)
        $rawFilePath = 'spectra-files/'.$device->name.'/spectra-connect-raw.xls';
        $engunitFilePath = 'spectra-files/'.$device->name.'/spectra-connect-engunit.xls';

        $rawLines = \Storage::disk('local')->get($rawFilePath);
        $engunitLines = \Storage::disk('local')->get($engunitFilePath);

        $rawResults = $this->loadSpectraConnectXlsInArray($rawLines);
        $engunitResults = $this->loadSpectraConnectXlsInArray($engunitLines);


        // find lastest log time_stamp from that spectra device to avoid saving old logs from files
        $lastTime = Spectra::where('device_name', $device->name)->max('time_stamp', 'DESC');

        // Replace tank_level_1 and tank_level_2 psi value (engunit) to percentage value (raw)
        // and return only the engunit data (English Unit measures)
        foreach($engunitResults as $key => $engunit) {
            // 2 files data might be different because of the time elapsed between the 2 download files
            // So check for existence of key
            if(isset($rawResults[$key]['ID'])) {
                $engunitResults[$key]['tnk_lvl_1'] = $rawResults[$key]['tnk_lvl_1'] . '%';
                $engunitResults[$key]['tnk_lvl_2'] = $rawResults[$key]['tnk_lvl_2'] . '%';
                $engunitResults[$key]['device_name'] = $device->name;
            }

            // If it's the first time for this Spectra Device OR the last time for it is older than the new times, then insert record
            if(!$lastTime || new DateTime($engunitResults[$key]['time_stamp']) > new DateTime($lastTime)) {
                Spectra::insertIgnore($engunitResults[$key]);
            }
        }

        \Log::channel('spectlog')->debug('Spectra '.$device->name.' XLS Files data is saved to DB');

        return response()->json(['success' => true, 'read_on' => (new DateTime())->format('Y-m-d H:i:s')]);

    }

    public function downloadRawXls(Request $request)
    {
        $filePath = 'sensor_raw_20190523_225354.xls';
        \Log::channel('spectlog')->debug('Spectra Engunit XLS File Downloaded');
        return response()->download(storage_path("app/public/{$filePath}"));
    }

    public function downloadEngunitXls(Request $request)
    {
        $filePath = 'sensor_engunit_20190523_225402.xls';
        \Log::channel('spectlog')->debug('Spectra Engunit XLS File Downloaded');
        return response()->download(storage_path("app/public/{$filePath}"));
    }

    public function uploadXlsFiles(Request $request) {

        try {
            $fileRaw = $request->file('spectra-raw');
            $fileEngunit = $request->file('spectra-engunit');

            \Log::channel('spectlog')->debug('File received : ' . $fileRaw->getClientOriginalName());
            \Log::channel('spectlog')->debug('File received : ' . $fileEngunit->getClientOriginalName());

            $spectraId = $request->input('spectraId');

            // check device existence
            $device = Device::where('name', $spectraId)->where('type', 'spectra')->first();
            if(!$device) {
                $device = new Device();
                $device->name = $spectraId;
                $device->type = 'spectra';
                $device->save();
            }


            \Storage::disk('local')->putFileAs(
                'spectra-files/'.$device->name,
                $fileRaw,
                'spectra-connect-raw.xls'
            );

            \Log::channel('spectlog')->debug('Spectra ' . $device->name . ' Raw XLS File Saved');

            \Storage::disk('local')->putFileAs(
                'spectra-files/'.$device->name,
                $fileEngunit,
                'spectra-connect-engunit.xls'
            );

            \Log::channel('spectlog')->debug('Spectra ' . $device->name . ' Engunit XLS File Saved');

            return '<br/>Spectra files are saved';

            
        } catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        //return response()->json(['data' => $data]);
    }

    private function loadSpectraConnectXlsInArray($lines) {
        $results = [];

        $rows = explode("\n", $lines);

        // For headers name like : ID, time_stamp, tnk_lvl_1, etc.
        $firstCells = explode("\t", str_replace(",", "\t", $rows[0]));

        foreach($rows as $rowIndex => $row) {

            // Row 0 is headers, skip
            if($rowIndex === 0) {
                //echo '****' . $cell;
                continue;
            }

            $result = [];
            $cells = explode("\t", $row);

            foreach($cells as $cellIndex => $cell) {

                // remove invisible characters
                $cell = str_replace(["\t", "\n\r", "\n", "\r"], '', $cell);

                // convert cell to number
                if(is_numeric($cell)) {
                    $cell = (float) $cell;
                }


                $headerCell = str_replace([" ", "\t", "\n\r", "\n", "\r"], '', $firstCells[$cellIndex]);
                if($headerCell) {
                    $result[$headerCell] = $cell;
                }

                /*if($headerCell == "time_stamp" && strlen($cell) > 5 && \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $cell)) {
                    echo $cell.' -- ';
                }*/

                //echo '****' . $cell;
            }

            if(count($result) > 1) {
                array_push($results, $result);
            }
        }

        return $results;
    }

}
