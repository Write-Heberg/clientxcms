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

class NotifySubscriberEmail extends Notification
{
    use Queueable, SerializesModels;

    private bool $firstMessage;
    private string $message;
    private SupportTicket $ticket;

    public function __construct(SupportTicket $ticket, string $message, bool $firstMessage = false)
    {
        $this->ticket = $ticket;
        $this->message = $message;
        $this->firstMessage = $firstMessage;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return $this->firstMessage
            ? $this->firstMessageMail($notifiable)
            : $this->replyMessageMail($notifiable);
    }

    public function firstMessageMail($notifiable)
    {
        $ticketUrl = route('admin.support.tickets.show', $this->ticket->id);
        return EmailTemplate::getMailMessage("support_admin_ticket_created", $ticketUrl, [
            'ticket' => $this->ticket,
            'message' => $this->message,
        ]);
    }

    public function replyMessageMail($notifiable)
    {
        $ticketUrl = route('admin.support.tickets.show', $this->ticket->id);
        return EmailTemplate::getMailMessage("support_admin_ticket_reply", $ticketUrl, [
            'ticket' => $this->ticket,
            'message' => $this->message,
        ]);
    }

}
