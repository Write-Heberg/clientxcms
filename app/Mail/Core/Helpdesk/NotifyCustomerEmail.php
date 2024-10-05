<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Mail\Core\Helpdesk;

use App\Models\Admin\EmailTemplate;
use App\Models\Helpdesk\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class NotifyCustomerEmail extends Notification
{

    use Queueable, SerializesModels;

    private string $message;
    private SupportTicket $ticket;

    public function __construct(SupportTicket $ticket, string $message)
    {
        $this->ticket = $ticket;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $ticketUrl = route('front.support.show', $this->ticket->id);
        return EmailTemplate::getMailMessage("support_customer_ticket_reply", $ticketUrl, [
            'ticket' => $this->ticket,
            'message' => $this->message,
        ]);
    }
}
