<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Contracts\Provisioning;

use App\Models\Provisioning\Service;

interface CardAdminServiceInterface
{
    public function renderAdmin(Service $service);
    public function validate(): array;
    public function update(Service $service, array $data = []);
}
