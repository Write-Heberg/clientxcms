<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Events\Core\Service;

use App\DTO\Provisioning\ServiceStateChangeDTO;
use App\Models\Provisioning\Service;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceDelivered extends ServiceEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ServiceStateChangeDTO $dto;

    public function __construct(Service $invoice, ServiceStateChangeDTO $dto)
    {
        parent::__construct($invoice);
        $this->dto = $dto;
    }
}
