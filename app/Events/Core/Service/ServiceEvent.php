<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Events\Core\Service;

use App\Models\Provisioning\Service;

abstract class ServiceEvent
{
    public Service $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }
}
