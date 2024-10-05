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
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class NotifyExpirationEmail extends Notification
{
    use Queueable, SerializesModels;

    private int $days;
    private Service $service;
    public function __construct(Service $service, int $days)
    {
        $this->service = $service;
        $this->days = $days;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $serviceUrl = route('front.services.show', $this->service->id);
        $context = [
            'service' => $this->service,
            'days' => $this->days,
        ];
        return EmailTemplate::getMailMessage('notify_expiration', $serviceUrl, $context);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
