<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Core;

use App\DTO\Core\Gateway\GatewayUriDTO;
use App\Models\Traits\ModelStatutTrait;
use App\Services\Core\PaymentTypeService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Gateway extends Model
{
    use HasFactory;
    use ModelStatutTrait;

    protected $fillable = [
        'name',
        'status',
        'uuid',
    ];

    public function paymentType()
    {
        return app(PaymentTypeService::class)->get($this->uuid);
    }

    public function createPayment(Invoice $invoice, Request $request)
    {
        $dto = new GatewayUriDTO($this, $invoice);
        return $this->paymentType()->createPayment($invoice, $this, $request, $dto);
    }

    public function processPayment(Invoice $invoice, Request $request)
    {
        $dto = new GatewayUriDTO($this, $invoice);
        return $this->paymentType()->processPayment($invoice, $this, $request, $dto);
    }

    public function getAttribute($key)
    {
        return parent::getAttribute($key);
    }

    public function getGatewayName()
    {
        if ($this->uuid == 'balance' && auth()->user() && auth()->user()->balance != 0){
            return $this->name . ' ('.formatted_price(auth()->user()->balance, currency()).')';
        } else {
            return $this->name;
        }
    }
}
