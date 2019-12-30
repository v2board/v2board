<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
        // v2board
        $schedule->command('v2board:cache')->hourly();
        // check
        $schedule->command('check:order')->everyMinute();
        $schedule->command('check:expire')->everyMinute();
        $schedule->command('check:commission')->everyMinute();
        // reset
        $schedule->command('reset:traffic')->monthlyOn(1, '00:00');
        $schedule->command('reset:serverLog')->monthlyOn(1, '00:00');
        // send
        $schedule->command('send:remindMail')->dailyAt('11:30');
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
