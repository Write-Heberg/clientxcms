<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Helpdesk;

use App\Events\Helpdesk\HelpdeskTicketAnsweredCustomer;
use App\Events\Helpdesk\HelpdeskTicketAnsweredStaff;
use App\Events\Helpdesk\HelpdeskTicketCreatedEvent;
use App\Mail\Core\Helpdesk\NotifyCustomerEmail;
use App\Mail\Core\Helpdesk\NotifySubscriberEmail;
use App\Models\Account\Customer;
use App\Models\Admin\Admin;
use App\Models\Core\Invoice;
use App\Models\Provisioning\Service;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SupportTicket extends Model
{
    use HasFactory;

    const STATUS_OPEN = 'open';
    const STATUS_CLOSED = 'closed';
    const FILTERS = [
        'all' => 'all',
        self::STATUS_OPEN => 'open',
        self::STATUS_CLOSED => 'closed',
    ];
    const PRIORITIES = [
        'low' => 'low',
        'medium' => 'medium',
        'high' => 'high',
    ];

    protected $fillable = [
        'department_id',
        'customer_id',
        'status',
        'priority',
        'subject',
        'related_type',
        'related_id',
        'staff_subscribers',
        'closed_at'
    ];

    protected $casts = [
        'staff_subscribers' => 'array',
        'closed_at' => 'datetime',
    ];

    public static function getPriorities()
    {
        return collect(self::PRIORITIES)->mapWithKeys(function ($value, $key) {
            return [$key => __('client.support.priorities.' . $key)];
        });
    }

    public function priorityLabel()
    {
        return __('client.support.priorities.' . $this->priority);
    }

    public function department()
    {
        return $this->belongsTo(SupportDepartment::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class, 'ticket_id');
    }

    public function messages()
    {
        return $this->hasMany(SupportMessage::class, 'ticket_id');
    }

    public function related()
    {
        $related = null;
        if ($this->related_type == 'service') {
            $related = $this->belongsTo(Service::class, 'related_id');
        }
        if ($this->related_type == 'invoice') {
            $related = $this->belongsTo(Invoice::class, 'related_id');
        }
        return $related;
    }

    public function isValidRelated()
    {
        if ($this->related_type == 'service') {
            return Service::where('id', $this->related_id)->exists();
        }
        if ($this->related_type == 'invoice') {
            return Invoice::where('id', $this->related_id)->exists();
        }
        return false;
    }


    public function notifySubscriber(Admin $subscriber, string $message, bool $firstMessage)
    {
        $subscriber->notify(new NotifySubscriberEmail($this, $message, $firstMessage));
    }

    public function notifyCustomer(string $message)
    {
        $this->customer->notify(new NotifyCustomerEmail($this, $message));
    }

    public function addMessage(string $content, ?int $customerId = null, ?int $staffId = null)
    {
        $isSpam = false;
        $lastMessage = $this->messages()->latest()->first();
        if ($lastMessage != null) {
            /** @var Carbon $createdAt */
            $createdAt = $lastMessage->created_at;
            if ($createdAt->diffInSeconds() < 10) {
                $isSpam = true;
            }
        }
        if ($isSpam) {
            return;
        }
        $message = new SupportMessage();
        $message->fill([
            'message' => $content,
            'customer_id' => $customerId,
            'admin_id' => $staffId,
        ]);
        $firstMessage = $this->messages()->count() == 0;
        $this->messages()->save($message);
        if ($customerId != null) {
            $subscribers = $this->department->staff_subscribers ?? [];
            $subscribers = array_merge($subscribers, $this->staff_subscribers ?? []);
            $subscribers = array_unique($subscribers);
            foreach ($subscribers as $subscriber) {
                $this->notifySubscriber($subscriber, $message, $firstMessage);
            }
        } else {
            $this->notifyCustomer($message);
        }
        if ($firstMessage) {
            event(new HelpdeskTicketCreatedEvent($this, $message));
        } else {
            if ($customerId != null) {
                event(new HelpdeskTicketAnsweredCustomer($this, $message));
            } else {
                event(new HelpdeskTicketAnsweredStaff($this, $message));
            }
        }
    }

    public function attachedUsers(){
        $users = [];
        foreach ($this->messages as $message){
            if($message->customer_id != null){
                $initial = $message->customer->firstname[0] . $message->customer->lastname[0];
                $users[$initial] = $message->customer->fullName;
            }
            if($message->admin_id != null){
                $initial = $message->admin->firstname[0] . $message->admin->lastname[0];
                $users[$initial] = $message->admin->username;
            }
        }
        return $users;

    }

    public function isOpen(){
        return $this->status == self::STATUS_OPEN;
    }

    public function isClosed(){
        return $this->status == self::STATUS_CLOSED;
    }

    public function close(bool $force = false){
        $this->status = self::STATUS_CLOSED;
        $this->closed_at = now();
        $this->save();
    }

    public function reopen(){
        $this->status = self::STATUS_OPEN;
        $this->closed_at = null;
        $this->save();
    }

    public function reply(string $content)
    {
        $this->addMessage($content, auth()->id());
        $this->notifyCustomer($content);
    }

    public function relatedValue()
    {
        if ($this->related_id == null) {
            return null;
        }
        return "{$this->related_type}-{$this->related_id}";
    }

    public function addAttachment(UploadedFile $attachment, int $customerId = null, int $staffId = null)
    {
        $lastMessage = $this->messages()->latest()->first();
        $folder = "helpdesk/attachments/{$this->id}/";
        $attachmentName = $attachment->getClientOriginalName();
        $attachmentName = str_replace(" ", "_", $attachmentName);
        $attachmentName = rand(1000, 9999) . '_' . $attachmentName;
        $attachment->storeAs($folder, $attachmentName);
        $file = new TicketAttachment();
        $file->fill([
            'filename' => $attachment->getClientOriginalName(),
            'path' => 'helpdesk/attachments/' . $this->id . '/'.$attachmentName,
            'mime' => $attachment->getClientMimeType(),
            'size' => $attachment->getSize(),
            'ticket_id' => $this->id,
            'customer_id' => $customerId,
            'admin_id' => $staffId,
            'message_id' => $lastMessage->id ?? null,
        ]);
        $file->save();
    }
    protected static function newFactory()
    {
        return \Database\Factories\Helpdesk\TicketFactory::new();
    }
}
