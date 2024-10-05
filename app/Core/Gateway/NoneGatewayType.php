<?php

namespace App\Core\Gateway;

use App\DTO\Core\Gateway\GatewayUriDTO;
use App\Exceptions\WrongPaymentException;
use App\Models\Core\Gateway;
use App\Models\Core\Invoice;
use App\Services\Store\GatewayService;
use Illuminate\Http\Request;

class NoneGatewayType extends \App\Abstracts\AbstractGatewayType
{
    const UUID = 'none';
    protected string $name = 'None';
    protected string $uuid = self::UUID;
    protected string $image = 'none.png';
    protected string $icon = 'bi bi-x-circle';
    public function createPayment(Invoice $invoice, Gateway $gateway, Request $request, GatewayUriDTO $dto)
    {
    }

    public function processPayment(Invoice $invoice, Gateway $gateway, Request $request, GatewayUriDTO $dto)
    {
    }

    public function saveConfig(array $data)
    {
        Gateway::where('uuid', self::UUID)->update([
            'status' => 'hidden',
        ]);
        GatewayService::forgotAvailable();
    }
}
