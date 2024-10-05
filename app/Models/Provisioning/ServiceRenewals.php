<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Provisioning;

use App\Models\Core\Invoice;
use Database\Factories\Provisioning\ServiceRenewalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRenewals extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'invoice_id',
        'start_date',
        'end_date',
        'renewed_at',
        'next_billing_on',
        'period',
        'first_period',
    ];
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'renewed_at' => 'datetime',
        'next_billing_on' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public static function findServiceByInvoiceItem(\App\Models\Core\InvoiceItem $item)
    {
        return self::where('invoice_id', $item->invoice_id)
            ->first();
    }


    protected static function newFactory()
    {
        return ServiceRenewalFactory::new();
    }
}
