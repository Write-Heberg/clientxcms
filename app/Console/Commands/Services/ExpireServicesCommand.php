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

class ExpireServicesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will expire services that are due for expiration.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Running services:expire at ' . now()->format('Y-m-d H:i:s'));

        $services = Service::getShouldExpire();
        foreach ($services as $service){
            $result = $service->expire();
            if ($result->success) {
                $this->info($result->message);
            } else {
                $this->error($result->message);
            }
        }
        $services = Service::getShouldSuspend();
        foreach ($services as $service){
            $result = $service->suspend(__('client.alerts.suspended_reason_expired'));
            if ($result->success) {
                $this->info($result->message);
            } else {
                $this->error($result->message);
            }
        }
        $services = Service::getShouldCancel();
        /** @var Service $service */
        foreach ($services as $service){
            $service->markAsCancelled();
            $this->info('Service ' . $service->id . ' has been marked as cancelled.');
        }

    }
}
