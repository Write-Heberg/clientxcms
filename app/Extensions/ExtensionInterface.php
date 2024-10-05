<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Extensions;

use App\DTO\Core\Extensions\ExtensionDTO;
use App\DTO\Core\Extensions\ExtensionInstallDTO;
use Composer\Autoload\ClassLoader;
use Illuminate\Foundation\Application;

interface ExtensionInterface
{
    public function onInstall(string $uuid): void;
    public function onUninstall(string $uuid): void;

    public function onEnable(string $uuid): void;
    public function onDisable(string $uuid): void;
    public function download(string $uuid): ExtensionInstallDTO;
    public function autoload(ExtensionDTO $DTO, Application $application, ClassLoader $composer):void;
    public function getExtensions(bool $enabledOnly = false): array;
}
