<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Console\Commands\Services;

use App\Models\Provisioning\Service;
use Illuminate\Console\Command;

class NotifyExpirationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:notify-expiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will notify users of services that are due for expiration.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Running services:notify-expiration at ' . now()->format('Y-m-d H:i:s'));
        $days = explode(',', setting('core_services_notify_expiration_days', '7,3,1'));
        if ($days == null){
            return;
        }
        /** @var Service[] $services */
        $services = Service::getShouldNotifyExpiration($days);
        foreach ($services as $service) {
            if ($service->notifyExpiration())
                $this->info("Service {$service->id} notified of expiration");
        }
    }
}
