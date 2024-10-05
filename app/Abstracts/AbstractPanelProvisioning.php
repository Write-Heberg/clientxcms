<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Abstracts;

use App\Contracts\Provisioning\CardAdminServiceInterface;
use App\Contracts\Provisioning\PanelProvisioningInterface;
use App\Models\Provisioning\Service;

abstract class AbstractPanelProvisioning implements PanelProvisioningInterface
{
    protected string $uuid;
    public function render(Service $service, array $permissions = [])
    {
        return 'Empty panel';
    }

    public function renderAdmin(Service $service)
    {
        return 'Empty panel';
    }

    public function permissions(): array
    {
        return [];
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function tabs(Service $service): array
    {
        return [];
    }

    public function cardAdmin(Service $service): ?CardAdminServiceInterface
    {
        return null;
    }
}
