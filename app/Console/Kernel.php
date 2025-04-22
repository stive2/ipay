<?php

namespace App\Console;

use App\Http\Controllers\Admin\SetupSMSController;
use App\Http\Controllers\Admin\WebSettingsController;
use App\Http\Controllers\GlobalController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            WebSettingsController::autoCollect();
            // SetupSMSController::sendSoldes();
        })->everyMinute();

        $schedule->command('queue:retry all')->daily();
        $schedule->command('queue:work --stop-when-empty')
            ->everyFiveMinutes()
            ->withoutOverlapping();
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
