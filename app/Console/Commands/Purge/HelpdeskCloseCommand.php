<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Console\Commands\Purge;

use Illuminate\Console\Command;

class HelpdeskCloseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clientxcms:helpdesk-close';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close helpdesk tickets.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Running services:expire at ' . now()->format('Y-m-d H:i:s'));
        $this->info('Closing helpdesk tickets...');
        $days = setting('helpdesk_ticket_auto_close_days', 7);
        if ($days <= 0) {
            $this->info('Auto close is disabled.');
            return;
        }
        $date = now()->subDays($days);
        $tickets = \App\Models\Helpdesk\SupportTicket::where('status', 'open')->where('updated_at', '<', $date)->get();
        foreach ($tickets as $ticket) {
            $ticket->close(true);
            $this->info('Ticket #' . $ticket->id . ' closed.');
        }
        $this->info('Helpdesk tickets closed.');
    }
}
