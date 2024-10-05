<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Console\Commands\Services;

use App\Models\Provisioning\Service;
use App\Services\Core\InvoiceService;
use Illuminate\Console\Command;

class CreateRenewalsInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:renewals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will create invoices for services that are due for renewal.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $services = Service::getShouldCreateInvoice();
        $this->info('Running services:renewals at ' . now()->format('Y-m-d H:i:s'));
        foreach ($services as $service){
            $invoice = InvoiceService::createInvoiceFromService($service);
            logger()->info("Created invoice for service #{$service->id}");
            $service->invoice_id = $invoice->id;
            $service->save();
            $this->info("Created invoice for service #{$service->id}");
        }
    }
}
