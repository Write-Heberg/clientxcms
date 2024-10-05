<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Mail\Core\Invoice;

use App\Models\Admin\EmailTemplate;
use App\Models\Core\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class InvoicePaidEmail extends Notification
{
    use Queueable, SerializesModels;

    private Invoice $invoice;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $invoiceUrl = route('front.invoices.show', $this->invoice->id);
        $context = [
            'invoice' => $this->invoice,
        ];
        return EmailTemplate::getMailMessage("invoice_paid", $invoiceUrl, $context);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachment = Attachment::fromData(function() {
            return $this->invoice->pdf();
        }, $this->invoice->identifier() . '.pdf', [
            'mime' => 'application/pdf',
        ]);
        return [$attachment];
    }
}
