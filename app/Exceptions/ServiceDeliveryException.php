<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Exceptions;

use App\Models\Provisioning\Service;

class ServiceDeliveryException extends \Exception
{
    private Service $service;

    public function __construct(string $message, Service $service, int $code)
    {
        parent::__construct('Service delivery Exception : ' . $message, $code, null);
        $this->service = $service;
    }
}
