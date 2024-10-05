<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Abstracts;

use App\Contracts\Store\GatewayTypeInterface;
use App\DTO\Core\Gateway\GatewayUriDTO;
use App\Models\Core\Gateway;
use App\Models\Core\Invoice;
use App\Models\Core\Subscription;
use Illuminate\Http\Request;

abstract class AbstractGatewayType implements GatewayTypeInterface
{
    protected string $name;
    protected string $uuid;
    protected string $image;

    protected string $icon;

    public function icon():string
    {
        return $this->icon;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function checkoutForm(array $context = [])
    {
        return '';
    }

    public function configForm(array $context = [])
    {
        return '';
    }

    public function saveConfig(array $data)
    {
        return;
    }

    public function validate(): array
    {
        return [
            'secret-key' => ['required', 'string'],
            'public-key' => ['required', 'string'],
            'endpoint-secret' => ['nullable', 'string'],
        ];
    }

    public function image(): string
    {
        return \Vite::asset('resources/global/' . $this->image);
    }

    public function notification(Gateway $gateway, Request $request) {
        return abort(404);
    }

    public abstract function createPayment(Invoice $invoice, Gateway $gateway, Request $request, GatewayUriDTO $dto);
    public abstract function processPayment(Invoice $invoice, Gateway $gateway, Request $request, GatewayUriDTO $dto);
    public function createSubscription(Invoice $invoice, Gateway $gateway, Request $request, GatewayUriDTO $dto): ?Subscription {
        return null;
    }
    public function cancelSubscription(Subscription $subscription):?Subscription{
        return null;
    }
}
