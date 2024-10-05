<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Console;

use App\Models\Admin\Setting;
use App\Models\Core\InvoiceItem;
use App\Models\Core\TaskResult;
use App\Models\Provisioning\Service;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        if (!is_installed()) {
            return;
        }
        $schedule->command('services:delivery')
            ->name('services:delivery')
            ->sendOutputTo(storage_path('logs/services-delivery.log'))
            ->everyMinute();
        $schedule->command('services:expire')
            ->everyMinute()
            ->name('services:expire')
            ->sendOutputTo(storage_path('logs/services-expire.log'));
        $schedule->command('services:renewals')
            ->everyThreeHours()
            ->name('services:renewals')
            ->sendOutputTo(storage_path('logs/services-renewals.log'));
        $schedule->command('clientxcms:helpdesk-close')
            ->daily()->at('12:00')
            ->name('clientxcms:helpdesk-close')
            ->sendOutputTo(storage_path('logs/helpdesk-close.log'));
        $schedule->command('services:notify-expiration')
            ->daily()->at('09:00')
            ->name('services:notify-expiration')
            ->sendOutputTo(storage_path('logs/services-notify-expiration.log'));
        $schedule->command('clientxcms:invoice-delete')
            ->daily()->at('00:00')
            ->name('clientxcms:invoice-delete')
            ->sendOutputTo(storage_path('logs/invoice-delete.log'));
        $schedule->command('clientxcms:purge-metadata')
            ->weekly()->mondays()
            ->name('clientxcms:purge-metadata')
            ->sendOutputTo(storage_path('logs/purge-metadata.log'));
        $schedule->command('clientxcms:purge-metadata')
            ->weekly()->thursdays()
            ->name('clientxcms:purge-basket')
            ->sendOutputTo(storage_path('logs/purge-basket.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
