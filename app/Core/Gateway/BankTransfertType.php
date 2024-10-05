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
use App\Models\Admin\Setting;
use App\Models\Core\Gateway;
use App\Models\Core\Invoice;
use Illuminate\Http\Request;

class BankTransfertType extends AbstractGatewayType
{
    const UUID = 'bank_transfert';
    protected string $name = 'Bank Transfert';
    protected string $uuid = self::UUID;
    protected string $image = 'bank-transfert-icon.png';
    protected string $icon = 'bi bi-bank';

    public function createPayment(Invoice $invoice, Gateway $gateway, Request $request, GatewayUriDTO $dto)
    {
        \Session::flash('success', __('client.invoices.banktransfer.flash'));
        return redirect($dto->returnUri);
    }

    public function processPayment(Invoice $invoice, Gateway $gateway, Request $request, GatewayUriDTO $dto)
    {
        // nothing to do
        return redirect()->route('front.invoices.show', $invoice->id);
    }

    public function validate(): array
    {
        return [
            "bank_transfert_details" => ["required", "string", "max:1000"],
        ];
    }

    public function configForm(array $context = [])
    {
        return view('admin.settings.store.gateways.bank-transfert', $context);
    }

    public function saveConfig(array $data)
    {
        Setting::updateSettings($data);
    }
}
