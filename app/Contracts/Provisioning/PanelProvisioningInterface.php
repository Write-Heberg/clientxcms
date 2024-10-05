<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Contracts\Provisioning;

use App\DTO\Provisioning\ProvisioningTabDTO;
use App\Models\Provisioning\Service;

interface PanelProvisioningInterface
{
    public function uuid(): string;

    /**
     * @return array<ProvisioningTabDTO>
     */
    public function tabs(Service $service): array;

    public function render(Service $service, array $permissions = []);

    public function renderAdmin(Service $service);

    public function permissions():array;

    public function cardAdmin(Service $service): ?CardAdminServiceInterface;
}
