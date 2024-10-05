<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Helpdesk;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'path',
        'mime',
        'customer_id',
        'admin_id',
        'size',
        'ticket_id',
        'message_id'
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class);
    }

}
