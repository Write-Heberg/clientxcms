<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Abstracts;

use App\DTO\Provisioning\ServiceStateChangeDTO;
use App\Models\Provisioning\Server;
use App\Models\Provisioning\Service;
use App\Models\Store\Product;
use Illuminate\Validation\Validator;

abstract class AbstractServerType implements \App\Contracts\Provisioning\ServerTypeInterface
{

    protected string $uuid;
    protected string $title;
    /**
     * @inheritDoc
     */
    public function uuid(): string
    {
        return $this->uuid ?? 'default';
    }

    /**
     * @inheritDoc
     */
    public function title(): string
    {
        return $this->title ?? 'Default';
    }

    /**
     * @inheritDoc
     */
    public function findServer(Product $product): ?\App\Models\Provisioning\Server
    {
        return Server::whereType($this->uuid)->where('status', "active")->first();
    }

    /**
     * @inheritDoc
     */
    public function testConnection(array $params): \App\DTO\Provisioning\ConnectionResponse
    {
        throw new \Exception('No test Connection is available');
    }

    /**
     * @inheritDoc
     */
    public function validate(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function createAccount(Service $service): \App\DTO\Provisioning\ServiceStateChangeDTO
    {
        return new ServiceStateChangeDTO($service, false, 'Method createAccount must be implemented');
    }

    /**
     * @inheritDoc
     */
    public function suspendAccount(Service $service): \App\DTO\Provisioning\ServiceStateChangeDTO
    {
        return new ServiceStateChangeDTO($service, false, 'Method suspendAction must be implemented');
    }

    /**
     * @inheritDoc
     */
    public function unsuspendAccount(Service $service): \App\DTO\Provisioning\ServiceStateChangeDTO
    {
        return new ServiceStateChangeDTO($service, false, 'Method unsuspendAction must be implemented');
    }

    /**
     * @inheritDoc
     */
    public function expireAccount(Service $service): \App\DTO\Provisioning\ServiceStateChangeDTO
    {
        return new ServiceStateChangeDTO($service, false, 'Method expireAcccount must be implemented');

    }

    /**
     * @inheritDoc
     */
    public function onRenew(Service $service): \App\DTO\Provisioning\ServiceStateChangeDTO
    {
        return new ServiceStateChangeDTO($service, false, 'Method onRenew must be implemented');
    }

    /**
     * @inheritDoc
     */
    public function changePassword(Service $service, ?string $password = null): \App\DTO\Provisioning\ServiceStateChangeDTO
    {
        return new ServiceStateChangeDTO($service, false, 'Method changePassword must be implemented');
    }

    /**
     * @inheritDoc
     */
    public function changeName(Service $service, ?string $name = null): ?\App\DTO\Provisioning\ServiceStateChangeDTO
    {
        return new ServiceStateChangeDTO($service, false, 'Method changeName must be implemented');
    }

    public function importService(): ?\App\Contracts\Provisioning\ImportServiceInterface
    {
        return null;
    }

    public function isDomainRegistered(string $domain): bool
    {
        return false;
    }

    public function upgradeService(Service $service, Product $product): ServiceStateChangeDTO
    {
        return new ServiceStateChangeDTO($service, false, 'Method upgradeService must be implemented');
    }

    public function downgradeService(Service $service, Product $product): ServiceStateChangeDTO
    {
        return new ServiceStateChangeDTO($service, false, 'Method downgradeService must be implemented');
    }
}
