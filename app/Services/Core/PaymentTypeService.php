<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Services\Core;

use App\Contracts\Store\GatewayTypeInterface;
use App\Core\Gateway\BalanceType;
use App\Core\Gateway\BankTransfertType;
use App\Core\Gateway\PayPalExpressCheckoutType;
use App\Core\Gateway\PayPalMethodType;
use App\Core\Gateway\StripeType;
use Illuminate\Support\Collection;

class PaymentTypeService
{
    private Collection $paymentMethods;

    public function __construct()
    {
        $this->paymentMethods = collect([
            PayPalMethodType::UUID => PayPalMethodType::class,
            PayPalExpressCheckoutType::UUID => PayPalExpressCheckoutType::class,
            StripeType::UUID => StripeType::class,
            BalanceType::UUID => BalanceType::class,
            BankTransfertType::UUID => BankTransfertType::class,
        ]);
    }

    public function all()
    {
        return $this->paymentMethods;
    }

    public function get(string $uuid)
    {
        return app($this->paymentMethods->get($uuid));
    }

    public function add(string $uuid, string $class)
    {
        $this->paymentMethods = $this->paymentMethods->merge([$uuid => $class]);
    }
}
