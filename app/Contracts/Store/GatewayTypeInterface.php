<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Contracts\Store;

use App\DTO\Core\Gateway\GatewayUriDTO;
use App\Models\Core\Gateway;
use App\Models\Core\Invoice;
use App\Models\Core\Subscription;
use Illuminate\Http\Request;

interface GatewayTypeInterface
{

    public function name():string;
    public function uuid():string;

    public function icon():string;
    public function checkoutForm(array $context = []);
    public function configForm(array $context = []);
    public function saveConfig(array $data);
    public function validate():array;
    public function image():string;

    public function createPayment(Invoice $invoice, Gateway $gateway, Request $request, GatewayUriDTO $dto);
    public function processPayment(Invoice $invoice, Gateway $gateway, Request $request, GatewayUriDTO $dto);
    public function createSubscription(Invoice $invoice, Gateway $gateway, Request $request, GatewayUriDTO $dto);
    public function cancelSubscription(Subscription $subscription):?Subscription;
    public function notification(Gateway $gateway, Request $request);
}
