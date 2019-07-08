<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Device;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        // SPECTRA DEVICES -------

        $devices = Device::where('type', 'spectra')->get();

        foreach($devices as $device) {
            $schedule->call('App\Http\Controllers\SpectraController@readData', [$device->name])
                    ->daily()->at('22:00');
        }

        // -----------------------------



        // $schedule->command('inspire')
        //          ->hourly();

        //$schedule->call('App\Http\Controllers\TeslaPowerwall2Controller@metersAggregates')
        //    ->everyMinute();
        // O(n^2)

        /*
        $schedule->call(function () {
            //
        })->weekly()->mondays()->at('13:00');
        */

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
