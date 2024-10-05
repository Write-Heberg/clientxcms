<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Console\Commands\Purge;

use App\Models\Core\Invoice;
use Illuminate\Console\Command;

class InvoiceIndexationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clientxcms:index-invoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reindex invoices.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Running services:expire at ' . now()->format('Y-m-d H:i:s'));
        $prefix = setting('billing_invoice_prefix', 'CTX');
        $key = now()->format('Y-m');
        $key = $prefix . "-" . $key . "-%";
        $all = Invoice::where('invoice_number', 'like', $key)->get();
        $i = 1;
        foreach ($all as $invoice) {
            $invoice_number = $prefix . "-" . now()->format('Y-m') . "-" . str_pad($i, 4, '0', STR_PAD_LEFT);
            if ($invoice->invoice_number == $invoice_number) {
                $i++;
                continue;
            }
            $this->info('Reindexing invoice #' . $invoice->id . ' to ' . $invoice_number);
            $invoice->invoice_number = $invoice_number;
            $invoice->save();
            $i++;
        }
        $this->info('Invoices reindexed.');
    }
}
