<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Extensions;

class BaseAddonServiceProvider extends BaseModuleServiceProvider
{

    /**
     * Load database migrations
     * @return void
     */
    public function loadMigrations():void
    {
        $this->loadMigrationsFrom($this->addonPath('database/migrations'));
    }


    protected function loadTranslations(): void
    {
        $langPath = $this->addonPath('lang');

        $this->loadTranslationsFrom($langPath, $this->uuid);
    }


    protected function loadViews(): void
    {
        $viewsPath = $this->addonPath('views');
        if (!is_dir($viewsPath)) {
            return;
        }
        $adminPath = $this->addonPath('views/admin');
        if (is_dir($adminPath)) {
            $this->loadViewsFrom($adminPath, $this->uuid . '_admin');
        }
        $hasTheme = app('theme')->hasTheme();
        if ($hasTheme) {
            $themePath = app('theme')->themepath() . 'views/' . $this->uuid;
            if (is_dir($themePath)) {
                $this->loadViewsFrom($themePath, $this->uuid);
            }
        }
        $defaultPath = $this->addonPath('views/default');
        if (is_dir($defaultPath)) {
            $this->loadViewsFrom($defaultPath, $this->uuid . ($hasTheme ? '_default' : ''));
        }
    }

    public function addonPath(string $path = ''): string
    {
        return base_path('addons/' . $this->uuid . ($path ? '/' . $path : $path));
    }
}
