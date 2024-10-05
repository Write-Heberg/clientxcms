<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Mail\Auth;

use App\Models\Admin\Admin;
use App\Models\Admin\EmailTemplate;
use Illuminate\Auth\Notifications\ResetPassword;

class ResetPasswordEmail extends ResetPassword
{
    public function toMail($notifiable)
    {
        if ($notifiable instanceof Admin){
            $resetUrl = url(route('admin.password.reset', [
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));
            $mail = EmailTemplate::getMailMessage("reset", $resetUrl, ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire'), 'customer' => $notifiable]);
            $mail->metadata('disable_save', true);
            return $mail;
        }
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
        return EmailTemplate::getMailMessage("reset", $resetUrl, ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire'), 'customer' => $notifiable]);
    }
}
