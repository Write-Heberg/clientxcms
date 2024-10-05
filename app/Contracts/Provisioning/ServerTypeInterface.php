<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Contracts\Provisioning;

use App\DTO\Provisioning\ConnectionResponse;
use App\DTO\Provisioning\ServiceStateChangeDTO;
use App\Models\Provisioning\Server;
use App\Models\Provisioning\Service;
use App\Models\Store\Product;

interface ServerTypeInterface
{
    /**
     * @return string the uuid of the server type
     */
    public function uuid():string;

    /**
     * @return string the title of the server type
     */
    public function title():string;

    /**
     * @return Server|null
     * Find the server that this server type is associated with
     * TODO : add parameter with order model
     */
    public function findServer(Product $product): ?Server;

    /**
     * @param array $params
     * @return ConnectionResponse
     */
    public function testConnection(array $params):ConnectionResponse;

    /**
     * @param
     * validate data for test connection
     * @return array
     */
    public function validate(): array;


    /**
     * @return string
     * Create a new account on the server
     */
    public function createAccount(Service $service):ServiceStateChangeDTO;

    /**
     * @param Service $service
     * suspend the account on the server
     * @return ServiceStateChangeDTO
     */
    public function suspendAccount(Service $service):ServiceStateChangeDTO;

    /**
     * @param Service $service
     * unsuspend the account on the server
     * @return ServiceStateChangeDTO
     */
    public function unsuspendAccount(Service $service):ServiceStateChangeDTO;

    /**
     * @param Service $service
     * expire the account on the server
     * @return ServiceStateChangeDTO
     */
    public function expireAccount(Service $service):ServiceStateChangeDTO;

    /**
     * Trigger on service is renew
     * @param Service $service
     * @return ServiceStateChangeDTO
     */
    public function onRenew(Service $service):ServiceStateChangeDTO;

    /**
     * @param Service $service
     * @param string|null $password
     * change password of the account on the server if we can
     * if password is null, reply success if this implementation can change password on the server.
     * @return ServiceStateChangeDTO
     */
    public function changePassword(Service $service, ?string $password = null):ServiceStateChangeDTO;

    /**
     * @param Service $service
     * @param string|null $name
     * change name of the account on the server if we can
     * if name is null, reply success if this implementation can change name on the server.
     * @return ServiceStateChangeDTO
     */
    public function changeName(Service $service, ?string $name = null):?ServiceStateChangeDTO;

    /**
     * @return ImportServiceInterface|null
     * Return the import service implementation for this server type
     * If null, the import service will not be available
     */
    public function importService():?ImportServiceInterface;

    public function upgradeService(Service $service, Product $product):ServiceStateChangeDTO;

    public function downgradeService(Service $service, Product $product):ServiceStateChangeDTO;
}
