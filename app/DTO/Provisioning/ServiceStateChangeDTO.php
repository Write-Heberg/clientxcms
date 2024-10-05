<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\DTO\Provisioning;

use App\Models\Provisioning\Service;
use Carbon\Carbon;

class ServiceStateChangeDTO
{
    public Service $service;
    public bool $success;
    public string $message;
    public Carbon $created_at;
    public array $data = [];

    public function __construct(Service $service, bool $success, string $message, array $data = [])
    {
        $this->service = $service;
        $this->success = $success;
        $this->message = $message;
        $this->created_at = Carbon::now();
        $this->data = $data;
    }
}
