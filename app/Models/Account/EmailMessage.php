<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Account;

use App\Models\Admin\EmailTemplate;
use Database\Factories\Core\EmailMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailMessage extends Model
{
    use HasFactory;


    protected $fillable = [
        'subject',
        'content',
        'recipient',
        'recipient_id',
        'template',
    ];

    public function template()
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'recipient_id');
    }

    public static function newFactory()
    {
        return EmailMessageFactory::new();
    }
}
