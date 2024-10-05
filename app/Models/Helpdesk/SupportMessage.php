<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Helpdesk;

use App\Models\Account\Customer;
use App\Models\Admin\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'customer_id',
        'admin_id',
        'message'
    ];

    public function formattedMessage()
    {
        $parser = new \Parsedown();
        $parser->setSafeMode(true);
        return nl2br($parser->parse($this->message));
    }

    public function containerClasses(string $view = 'customer')
    {
        if ($view === 'customer') {
            return $this->customer_id != null ? 'max-w-lg flex gap-x-2 sm:gap-x-4' : 'max-w-lg ms-auto flex justify-end gap-x-2 sm:gap-x-4';
        }
        if ($view === 'admin') {
            return $this->admin_id != null ? 'max-w-lg ms-auto flex justify-end gap-x-2 sm:gap-x-4' : 'max-w-lg flex gap-x-2 sm:gap-x-4';
        }
    }

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function isStaff()
    {
        return $this->admin_id !== null;
    }

    public function isCustomer()
    {
        return $this->customer_id !== null;
    }

    public function staffUsername()
    {
        if ($this->admin === null)
            return "Deleted Staff";
        return $this->admin->username;
    }

    public function initials()
    {
        if ($this->customer_id != null)
            return $this->customer->initials();
        if ($this->admin_id != null) {
            if ($this->admin != null){
                return $this->admin->initials();
            }
        }
        return 'AB';
    }

    public function replyText(int $i, string $view = 'customer')
    {
        if ($this->customer_id != null){
            if ($i == 0){
                if ($view === 'customer')
                    return __('client.support.show.your_demand');
                return __('client.support.show.customer_demand', ['app' => setting('app_name')]);
            }
            if ($view === 'customer')
                return __('client.support.show.replybycustomer1');
            return __('client.support.show.replybycustomer2');
        }
        if ($i == 0) {
            return __('client.support.show.messagebystaff', ['app' => setting('app_name')]);
        }
        return __('client.support.show.replybystaff', ['app' => setting('app_name')]);
    }

    public function getAttachments()
    {
        return $this->ticket->attachments->where('message_id', $this->id);
    }

    public function hasAttachments()
    {
        return $this->getAttachments()->count() > 0;
    }

    public function getAttachmentsNames()
    {
        return $this->getAttachments()->pluck('file_name')->toArray();
    }
}
