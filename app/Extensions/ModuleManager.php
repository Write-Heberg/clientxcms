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
use App\Exceptions\ExtensionException;
use Composer\Autoload\ClassLoader;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;

class ModuleManager implements ExtensionInterface
{
    private Filesystem $files;
    public function __construct()
    {
        $this->files = new Filesystem();
    }

    public function modulePath(string $uuid, string $path = ''): string
    {
        return base_path('modules/' . $uuid . ($path ? '/' . $path : $path));
    }

    public function autoload(ExtensionDTO $DTO, Application $application, ClassLoader $composer): void
    {
        $uuid = $DTO->uuid;
        $file = $this->modulePath($uuid, 'composer.json');
        if (!$this->files->exists($file)) {
            return;
        }
        $composerJson = json_decode($this->files->get($file), true);;
        if (!$composerJson) {
            throw new ExtensionException(sprintf('Unable to read %s file', $file));
        }
        $autoload = $composerJson['autoload'] ?? [];
        foreach ($autoload['psr-4'] ?? [] as $namespace => $path) {
            if (!array_key_exists($namespace, $composer->getClassMap())) {
                $composer->addPsr4($namespace, $this->modulePath($uuid, $path));
            }
        }

        foreach ($autoload['files'] ?? [] as $file) {
            $this->files->getRequire($this->modulePath($uuid, $file));
        }
        $providers = $DTO->api['providers'] ?? $composerJson['providers'] ?? [];
        foreach ($providers as $provider) {
            if (!class_exists($provider['provider'])) {
                continue;
            }
            $application->register($provider['provider']);
        }
    }

    public function getExtensions(bool $enabledOnly = false): array
    {
        $modules = [];
        $read = ExtensionManager::readExtensionJson();
        foreach ($read['modules'] ?? [] as $module){
            if ($enabledOnly && !$module['enabled']){
                continue;
            }
            $module = ExtensionDTO::fromArray($module);

            if ($module->isActivable() && $enabledOnly){
                $modules[] = $module;
            }
            if (!$enabledOnly){
                $modules[] = $module;
            }
        }
        return $modules;
    }

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
}
