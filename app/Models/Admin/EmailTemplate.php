<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subject',
        'content',
        'button_text',
        'button_url',
        'hidden',
        'locale',
    ];

    public static function getMailMessage(string $name, string $url, array $context = [],  ?string $locale = null): MailMessage
    {
        if ($locale == null){
            $locale = setting('app_default_locale');
        }
        $template = self::where('name', $name)->where('locale', $locale)->first();
        if ($template == null){
            throw new \Exception(sprintf('Email template %s not found for locale %s', $name, $locale));
        }
        $content = \Blade::render($template->content, $context);
        $parts = explode('\n', $content);
        $parts = collect($parts)->map(function($part){
            return new HtmlString($part .'<br>');
        });
        $mail = (new MailMessage)
            ->greeting(setting('mail_greeting'))
            ->subject($template->subject)
            ->lines($parts)
            ->salutation(setting('mail_salutation'))
            ->action($template->button_text, $url);
        $mail->viewData = [
            'button_url' => $url,
            'button_text' => $template->button_text,
            'template' => $template->id,
        ];
        return $mail;
    }
}
