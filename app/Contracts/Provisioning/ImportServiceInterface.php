<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Contracts\Provisioning;

use App\DTO\Provisioning\ServiceStateChangeDTO;
use App\Models\Provisioning\Service;

interface ImportServiceInterface
{
    public function import(Service $service, array $data = []): ServiceStateChangeDTO;
    public function validate(): array;
    public function render(Service $service, array $data = []);
}
