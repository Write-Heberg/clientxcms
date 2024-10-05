<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\DTO\Core\Gateway;

use App\Models\Core\Gateway;
use App\Models\Core\Invoice;

class GatewayUriDTO
{
    public Gateway $gateway;
    public string $cancelUri;
    public string $returnUri;
    public string $notificationUri;

    public function __construct(Gateway $gateway, Invoice $invoice)
    {
        $this->gateway = $gateway;
        $this->cancelUri = route('gateways.cancel', ['gateway' => $gateway->uuid, 'invoice' => $invoice->id]);
        $this->returnUri = route('gateways.return', ['gateway' => $gateway->uuid, 'invoice' => $invoice->id]);
        $this->notificationUri = route('gateways.notification', ['gateway' => $gateway->uuid]);
    }
}
