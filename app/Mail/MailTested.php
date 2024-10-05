<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Mail;

use App\Models\Admin\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class MailTested extends Notification
{

    use Queueable, SerializesModels;

    private Admin $admin;

    public function __construct(Admin $admin)
    {
        $this->admin = $admin;
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage())
            ->subject('CLIENTXCMS Test Message')
            ->greeting('Hello ' . $this->admin->username . '!')
            ->metadata('disable_save', true)
            ->line('This is a test of the CLIENTXCMS mail system. You\'re good to go!');
    }
}
