<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Extensions;


use Illuminate\Support\ServiceProvider;

abstract class BaseModuleRouteServiceProvider extends ServiceProvider
{

    /**
     * Define the routes for the plugin.
     */
    abstract public function loadRoutes();

    /**
     * Bootstrap any plugin services.
     */
    public function boot(): void
    {
        if (! $this->app->routesAreCached()) {
            $this->loadRoutes();
        }
    }
}
