<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Console\Commands\Services;

use App\Models\Core\InvoiceItem;
use App\Models\Provisioning\Service;
use App\Models\Provisioning\ServiceRenewals;
use App\Services\Core\InvoiceService;
use Illuminate\Console\Command;

class ServicesDeliveryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:delivery';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deliver pending services to customers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Running services:delivery at ' . now()->format('Y-m-d H:i:s'));
        $this->deliverManualServices();
        $this->deliverInvoiceServices();
        $this->deliverRenewalServices();
    }

    private function deliverManualServices()
    {
        $services = Service::getItemsByMetadata('must_created_manually', '1');
        $services->each(function (Service $service) {
            try {
                $result = $service->deliver();
                if ($result->success){
                    $service->attachMetadata('must_created_manually', '0');
                    $this->info("Service {$service->id} delivered : " . $result->message);
                } else {
                    $this->error("Service {$service->id} delivery failed Error : " . $result->message);
                }
            } catch (\Exception $e) {
                $this->error("Service {$service->id} delivery failed : " . $e->getMessage());
            }
        });
    }

    private function deliverInvoiceServices()
    {
        $items = InvoiceItem::findServicesMustDeliver();
        $items->each(function (InvoiceItem $item) {
            try {
                $item->tryDeliver();
                $this->info("Service delivered for invoice item {$item->id}");
            } catch (\Exception $e) {
                $this->error("Service delivery failed for invoice item {$item->id} : " . $e->getMessage());
            }
        });
    }

    private function deliverRenewalServices()
    {
        $renewals = InvoiceItem::findPendingRenewals();
        $renewals->each(function (InvoiceItem $item) {
            try {
                $item->tryDeliver();
                $this->info("Renewal delivered for invoice item {$item->id}");
            } catch (\Exception $e) {
                $this->error("Renewal delivery failed for invoice item {$item->id} : " . $e->getMessage());
            }
        });
    }
}
