<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Extensions;

use App\DTO\Core\Extensions\ExtensionInstallDTO;
use Composer\Autoload\ClassLoader;
use Illuminate\Console\Application;

class ComponantManager implements ExtensionInterface
{

    public function onInstall(string $uuid): void
    {
        // TODO: Implement onInstall() method.
    }

    public function onUninstall(string $uuid): void
    {
        // TODO: Implement onUninstall() method.
    }

    public function onEnable(string $uuid): void
    {
        // TODO: Implement onEnable() method.
    }

    public function onDisable(string $uuid): void
    {
        // TODO: Implement onDisable() method.
    }

    public function download(string $uuid): ExtensionInstallDTO
    {
        // TODO: Implement download() method.
    }

    public function autoload(string $uuid, \Illuminate\Foundation\Application $application, ClassLoader $composer): void
    {
        // TODO: Implement autoload() method.
    }

    public function getExtensions(bool $enabledOnly = false): array
    {
        // TODO: Implement getExtensions() method.
    }
}
