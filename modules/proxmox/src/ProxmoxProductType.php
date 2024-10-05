<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox;

use App\Abstracts\AbstractProductType;
use App\Abstracts\WebHostingProductData;
use App\Contracts\Store\ProductConfigInterface;
use App\Models\Store\Product;

class ProxmoxProductType extends AbstractProductType
{
    protected string $uuid = "proxmox";
    protected string $title = "Proxmox";
    protected string $type = self::SERVICE;

    public function server(): ?\App\Contracts\Provisioning\ServerTypeInterface
    {
        return new ProxmoxServerType();
    }

    public function config(): ?ProductConfigInterface
    {
        return new ProxmoxConfig();
    }

    public function data(?Product $product=null): ?\App\Contracts\Store\ProductDataInterface
    {
        return new ProxmoxData();
    }

    public function panel(): ?\App\Contracts\Provisioning\PanelProvisioningInterface
    {
        return new ProxmoxPanel();
    }
}
