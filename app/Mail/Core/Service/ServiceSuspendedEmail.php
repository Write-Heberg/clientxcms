<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Mail\Core\Service;

use App\Models\Admin\EmailTemplate;
use App\Models\Provisioning\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class ServiceSuspendedEmail extends Notification
{
    use Queueable, SerializesModels;

    private Service $service;
    private string $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(Service $service, string $reason)
    {
        $this->service = $service;
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $invoiceUrl = route('front.services.show', $this->service->id);
        $context = [
            'service' => $this->service,
            'reason' => $this->reason,
        ];
        return EmailTemplate::getMailMessage("service_suspended", $invoiceUrl, $context);
    }
}
