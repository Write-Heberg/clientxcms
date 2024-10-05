<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Core\Gateway;

use App\Abstracts\AbstractGatewayType;
use App\DTO\Core\Gateway\GatewayUriDTO;
use App\Helpers\EnvEditor;
use App\Models\Core\Gateway;
use App\Models\Core\Invoice;
use Illuminate\Http\Request;
use Str;

class BalanceType extends AbstractGatewayType
{
    const UUID = 'balance';
    protected string $name = 'Balance';
    protected string $uuid = self::UUID;
    protected string $image = 'balance-icon.png';
    protected string $icon = 'bi bi-currency-dollar';

    public function createPayment(Invoice $invoice, Gateway $gateway, Request $request, GatewayUriDTO $dto)
    {
        $transactionId = "ctx-". Str::uuid();
        $invoice->update(['external_id' => $transactionId]);
        return redirect($dto->returnUri);
    }

    public function processPayment(Invoice $invoice, Gateway $gateway, Request $request, GatewayUriDTO $dto)
    {
        if ($invoice->total > $invoice->customer->balance) {
            $invoice->fail();
        } else {
            $invoice->customer->balance -= $invoice->total;
            $invoice->customer->save();
            $invoice->complete();
        }
        return redirect()->route('front.invoices.show', $invoice->id);
    }

    public function saveConfig(array $data)
    {
        EnvEditor::updateEnv([
            'STRIPE_PRIVATE_KEY' => $data['private_key'],
            'STRIPE_PUBLIC_KEY' => $data['public_key'],
            'STRIPE_WEBHOOK_SECRET' => $data['webhook_secret'],
            'STRIPE_PAYMENT_TYPES' => implode(',', $data['payment_types']),
        ]);
    }

    public function validate(): array
    {
        return [
            'private_key' => 'required|string',
            'public_key' => 'required|string',
            'webhook_secret' => 'required|string',
            'payment_types' => 'required|string|array',
        ];
    }

}
