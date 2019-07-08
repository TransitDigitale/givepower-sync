<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('tesla-powerwall2/meters/aggregates', 'TeslaPowerwall2Controller@metersAggregates')->name('tp2.meters.aggregate');

Route::post('tesla-powerwall2/meters/aggregates/push', 'TeslaPowerwall2Controller@pushMetersAggregates')->name('push.tp2.meters.aggregate');

Route::get('tesla-powerwall2/meters/test', 'TeslaPowerwall2Controller@metersTest')->name('tp2.meters.test');


// Spectra ---------
Route::get('spectra/read-data/{deviceName}', 'SpectraController@readData')->name('spectra.read.data');

Route::get('spectra/download-raw-xls', 'SpectraController@downloadRawXls')->name('spectra.download.raw.xls');

Route::get('spectra/download-engunit-xls', 'SpectraController@downloadEngunitXls')->name('spectra.download.engunit.xls');

Route::post('spectra/upload-xls-files', 'SpectraController@uploadXlsFiles')->name('spectra.upload.xls');


Route::resource('spectra', 'DeviceController');


