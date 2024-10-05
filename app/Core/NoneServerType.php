<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Core;

use App\Abstracts\AbstractServerType;
use App\Models\Provisioning\Service;
use App\Models\Store\Product;
use GuzzleHttp\Psr7\Response;

class NoneServerType extends AbstractServerType
{
    /**
     * Renvoie toujours null
     * @param Product $product
     * @return \App\Models\Provisioning\Server|null
     */
    public function findServer(Product $product): ?\App\Models\Provisioning\Server
    {
        return null;
    }

    public function createAccount(\App\Models\Provisioning\Service $service): \App\DTO\Provisioning\ServiceStateChangeDTO
    {
        return new \App\DTO\Provisioning\ServiceStateChangeDTO($service, true, 'Created account on none server type');
    }

    public function testConnection(array $params): \App\DTO\Provisioning\ConnectionResponse
    {
        $response = new Response(200, [], 'Test connection on none server type');
        return new \App\DTO\Provisioning\ConnectionResponse($response, 'Test connection on none server type');
    }

    public function suspendAccount(Service $service): \App\DTO\Provisioning\ServiceStateChangeDTO
    {
        return new \App\DTO\Provisioning\ServiceStateChangeDTO($service, true, 'Suspended account on none server type');
    }

    public function unsuspendAccount(Service $service): \App\DTO\Provisioning\ServiceStateChangeDTO
    {
        return new \App\DTO\Provisioning\ServiceStateChangeDTO($service, true, 'Unsuspended account on none server type');
    }

    public function expireAccount(Service $service): \App\DTO\Provisioning\ServiceStateChangeDTO
    {
        return new \App\DTO\Provisioning\ServiceStateChangeDTO($service, true, 'Deleted account on none server type');
    }

    public function onRenew(Service $service): \App\DTO\Provisioning\ServiceStateChangeDTO
    {
        return new \App\DTO\Provisioning\ServiceStateChangeDTO($service, true, 'Renewed account on none server type');
    }


}
