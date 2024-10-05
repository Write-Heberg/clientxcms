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

class InvoiceDeleteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clientxcms:invoice-delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Running services:expire at ' . now()->format('Y-m-d H:i:s'));
        $this->info('delete pending invoice...');
        $active = setting('remove_pending_invoice_type', 'cancel');
        $days = setting('remove_pending_invoice', 0);
        if ($days <= 0) {
            $this->info('Auto delete is disabled.');
            return;
        }
        $invoices = Invoice::where('status', 'pending')->where('created_at', '<', now()->subDays($days))->get();
        foreach ($invoices as $invoice) {
            if ($active === 'delete') {
                $invoice->items()->delete();
                $invoice->delete();
                $this->info('Invoice #' . $invoice->id . ' deleted.');
            } else {
                $invoice->cancel();
                $this->info('Invoice #' . $invoice->id . ' canceled.');
            }
        }
    }
}
