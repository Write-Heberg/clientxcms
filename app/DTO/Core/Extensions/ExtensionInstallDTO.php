<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\DTO\Core\Extensions;

class ExtensionInstallDTO
{
    public bool $isInstalled;
    public string $name;
    public string $version;
    public bool $isUpdated;
    public bool $isActivated;

    public function __construct(bool $isInstalled, string $name, string $version, bool $isUpdated, bool $isActivated)
    {
        $this->isInstalled = $isInstalled;
        $this->name = $name;
        $this->version = $version;
        $this->isUpdated = $isUpdated;
        $this->isActivated = $isActivated;
    }
}
