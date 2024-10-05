<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Events\Helpdesk;

use App\Models\Helpdesk\SupportMessage;
use App\Models\Helpdesk\SupportTicket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;

class HelpdeskTicketEvent extends Event
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public SupportTicket $ticket;
    public SupportMessage $message;

    public function __construct(SupportTicket $ticket, SupportMessage $message)
    {
        $this->ticket = $ticket;
        $this->message = $message;
    }
}
