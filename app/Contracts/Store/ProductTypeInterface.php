<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Contracts\Store;

use App\Contracts\Provisioning\PanelProvisioningInterface;
use App\Contracts\Provisioning\ServerTypeInterface;
use App\Models\Store\Product;

interface ProductTypeInterface
{

    const DOWNLOAD = "download";
    const SERVICE = "service";
    const DOMAIN = "domain";
    const GIFT_CARD = "gift_card";
    const LICENSE = "license";
    const OTHER = "other";
    const NONE = "none";
    const ALL = [
        self::DOWNLOAD,
        self::SERVICE,
        self::DOMAIN,
        self::GIFT_CARD,
        self::LICENSE,
        self::OTHER,
        self::NONE,
    ];

    /**
     * @return string UUID
     */
    public function uuid(): string;
    /**
     * @return string Title
     */
    public function title(): string;

    /**
     * @return string Type of provisioning (download, service, domain, gift_card, license, none, other)
     * Recommended : service
     */
    public function type():string;

    /**
     * @return ProductDataInterface|null Product data class (if any)
     * @param Product|null $product
     * If you want ask osname or other data to the user on new order
     */
    public function data(?Product $product = null): ?ProductDataInterface;

    /**
     * @return PanelProvisioningInterface|null Panel provisioning class (if any)
     */
    public function panel(): ?PanelProvisioningInterface;

    /**
     * @return ServerTypeInterface|null Server provisioning class (if any)
     * Recommanded if you want to create a service
     */
    public function server(): ?ServerTypeInterface;

    /**
     * @return ProductOptionsInterface[] Product additional options class (if any)
     */
    public function options(): array;

    /**
     *
     * @return ProductConfigInterface|null Product config class (if any)
     */
    public function config():?ProductConfigInterface;
}
