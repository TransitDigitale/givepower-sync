<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class TeslaPowerwall2Controller extends Controller
{
    protected $meterAggregatesURI = '/api/meters/aggregates';
    protected $meterSiteURI = '/api/meters/site';
    protected $meterSolarURI = '/api/meters/solar';
    //State of Charge / State of Energy 
    protected $systemStatusSoeURI = '/api/system_status/soe';
    protected $sitemasterURI = '/api/sitemaster';

    //
    public function metersAggregates(Request $request)
    {
    	// /api/meters/aggregates

    	$data0 = 
        '{
            site: {
                last_communication_time: "2018-04-02T16:11:41.885377469-07:00",
                instant_power: -21.449996948242188,
                instant_reactive_power: -138.8300018310547,
                instant_apparent_power: 140.47729986545957,
                frequency: 60.060001373291016,
                energy_exported: 1136916.6875890202,
                energy_imported: 3276432.6625890196,
                instant_average_voltage: 239.81999969482422,
                instant_total_current: 0,
                i_a_current: 0,
                i_b_current: 0,
                i_c_current: 0
            },
            battery: {
                last_communication_time: "2018-04-02T16:11:41.89022247-07:00",
                instant_power: -2350,
                instant_reactive_power: 0,
                instant_apparent_power: 2350,
                frequency: 60.033,
                energy_exported: 1169030,
                energy_imported: 1638140,
                instant_average_voltage: 239.10000000000002,
                instant_total_current: 45.8,
                i_a_current: 0,
                i_b_current: 0,
                i_c_current: 0
            },
            load: {
                last_communication_time: "2018-04-02T16:11:41.885377469-07:00",
                instant_power: 1546.2712597712405,
                instant_reactive_power: -71.43153973801415,
                instant_apparent_power: 1547.920305979569,
                frequency: 60.060001373291016,
                energy_exported: 0,
                energy_imported: 7191016.994444443,
                instant_average_voltage: 239.81999969482422,
                instant_total_current: 6.44763264839839,
                i_a_current: 0,
                i_b_current: 0,
                i_c_current: 0
            },
            solar: {
                last_communication_time: "2018-04-02T16:11:41.885541803-07:00",
                instant_power: 3906.1700439453125,
                instant_reactive_power: 53.26999855041504,
                instant_apparent_power: 3906.533259164868,
                frequency: 60.060001373291016,
                energy_exported: 5534272.949724403,
                energy_imported: 13661.930279959455,
                instant_average_voltage: 239.8699951171875,
                instant_total_current: 0,
                i_a_current: 0,
                i_b_current: 0,
                i_c_current: 0
            },
            busway: {
                last_communication_time: "0001-01-01T00:00:00Z",
                instant_power: 0,
                instant_reactive_power: 0,
                instant_apparent_power: 0,
                frequency: 0,
                energy_exported: 0,
                energy_imported: 0,
                instant_average_voltage: 0,
                instant_total_current: 0,
                i_a_current: 0,
                i_b_current: 0,
                i_c_current: 0
            },
            frequency: {
                last_communication_time: "0001-01-01T00:00:00Z",
                instant_power: 0,
                instant_reactive_power: 0,
                instant_apparent_power: 0,
                frequency: 0,
                energy_exported: 0,
                energy_imported: 0,
                instant_average_voltage: 0,
                instant_total_current: 0,
                i_a_current: 0,
                i_b_current: 0,
                i_c_current: 0
            },
            generator: {
                last_communication_time: "0001-01-01T00:00:00Z",
                instant_power: 0,
                instant_reactive_power: 0,
                instant_apparent_power: 0,
                frequency: 0,
                energy_exported: 0,
                energy_imported: 0,
                instant_average_voltage: 0,
                instant_total_current: 0,
                i_a_current: 0,
                i_b_current: 0,
                i_c_current: 0
            }
        }';

        // get data from Tesla Powerwall API
        $client = new Client();
        $response1 = $client->request('GET', 'http://localhost:8060/api/v1/direct/vendor/1?direct_key=Ks39d-Us4gD8-24sdUo');

        \Log::channel('tp2log')->debug('tesla powerwall 2 meters aggregates fetched');

        $data1 = $response1->getBody();

        // Post data to Givepower Server API
        $data2 = $client->request('POST', 'http://localhost:8070/tesla-powerwall2/meters/aggregates/push', [
            /*'headers' => [
                'Accept' => 'application/json',
                'X-XSRF-TOKEN' => ''
            ],*/
            'json' => json_decode($data1),
            //'json' => ['foo' => 'bar']
            //'http_errors' => false
        ]);

        \Log::channel('tp2log')->debug('tesla powerwall 2 meters aggregates pushed');

        //echo $data2->getStatusCode();

        //return $data2;
        //$data = json_encode($data);

        // Now push the code online


        /*
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', 'https://api.github.com/user', [
            'auth' => ['user', 'pass']
        ]);
        echo $res->getStatusCode();
        // 200
        echo $res->getHeaderLine('content-type');
        // 'application/json; charset=utf8'
        echo $res->getBody();
        // {"type":"User"...'

        // Send an asynchronous request.
        $request = new \GuzzleHttp\Psr7\Request('GET', 'http://httpbin.org');
        $promise = $client->sendAsync($request)->then(function ($response) {
            echo 'I completed! ' . $response->getBody();
        });
        $promise->wait();
        And make other kinds of requests like

        $response = $client->get('http://httpbin.org/get');
        $response = $client->delete('http://httpbin.org/delete');
        $response = $client->head('http://httpbin.org/get');
        $response = $client->options('http://httpbin.org/get');
        $response = $client->patch('http://httpbin.org/patch');
        $response = $client->post('http://httpbin.org/post');
        $response = $client->put('http://httpbin.org/put');
        */

        return 'tesla powerwall 2 meters aggregates fetched';

        // return the result
        //return response()->json($data);

    }


    public function pushMetersAggregates(Request $request)
    {
        $data = $request->json()->all();
        //\Log::channel('tp2log')->debug(json_encode($data));
        
        return response()->json([
            'message' => 'Successfully posted data',
            'data' => $data
        ]);
    }

    public function metersTest(Request $request)
    {
        \Log::channel('tp2log')->debug('tesla powerwall 2 meters test fetched');

        return response()->json(['message' => 'Successfully fetch test data']);
    }
}
