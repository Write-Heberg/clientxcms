<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Listeners;

use App\Models\Account\Customer;
use App\Models\Account\EmailMessage;
use Carbon\Carbon;
use Illuminate\Mail\Events\MessageSending;

class LogSentMessage
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MessageSending $event): void
    {
        if ($event->message->getHeaders()->has('x-metadata-disable_save')) {
            return;
        }
        $params = [
            'recipient' => $event->message->getTo()[0]->getAddress(),
            'subject' => $event->message->getSubject(),
            'content' => $event->message->getHtmlBody(),
            'recipient_id' => Customer::whereEmail($event->message->getTo()[0]->getAddress())->first()->id ?? null,
            'template' => $event->data['template'] ?? null,
            'created_at' => Carbon::now(),
        ];
        EmailMessage::insert($params);
    }
}
