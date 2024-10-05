<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Extensions;

use App\Http\Kernel;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

abstract class BaseModuleServiceProvider extends ServiceProvider
{
    /**
     * @var string The module name.
     */
    protected string $name;
    /**
     * @var string The module version.
     */
    protected string $version;
    /**
     * @var string The module UUID. Must be unique.
     */
    protected string $uuid;

    /**
     * The module's global HTTP middleware stack.
     */
    protected array $middleware = [];

    /**
     * The module's route middleware groups.
     */
    protected array $middlewareGroups = [];

    /**
     * The module's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     */
    protected array $routeMiddleware = [];

    /**
     * The policy mappings for this module.
     */
    protected array $policies = [];

    private Router $router;


    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->router = $app[Router::class];
    }

    /**
     * trigger on download module.
     * @return void
     */
    public function onDownload()
    {

    }

    /**
     * trigger on enable module.
     * @return void
     */
    public function onEnable()
    {

    }

    /**
     * trigger on disable module.
     * @return void
     */
    public function onDisable()
    {

    }

    /**
     * load plugin routes.
     * @return void
     */
    public function loadRoutes()
    {

    }
    /**
     * Load database migrations
     * @return void
     */
    public function loadMigrations():void
    {
        $this->loadMigrationsFrom($this->modulePath('database/migrations'));
    }

    /**
     * Load module path
     * @param string $path
     * @return string
     */
    public function modulePath(string $path = ''): string
    {
        return base_path('modules/' . $this->uuid . ($path ? '/' . $path : $path));
    }

    /**
     * Load module translation
     * @param string $path
     * @return string
     */
    protected function loadTranslations(): void
    {
        $langPath = $this->modulePath('lang');

        $this->loadTranslationsFrom($langPath, $this->uuid);
    }

    protected function middleware($middleware, bool $before = false): void
    {
        $kernel = $this->app->make(Kernel::class);

        foreach ((array) $middleware as $value) {
            if ($before) {
                $kernel->prependMiddleware($value);
            } else {
                $kernel->pushMiddleware($value);
            }
        }
    }


    protected function registerMiddleware(): void
    {
        $this->middleware($this->middleware);

        $this->middlewareGroup($this->middlewareGroups);

        $this->routeMiddleware($this->routeMiddleware);
    }

    protected function middlewareGroup(string|array $name, array $middleware = null): void
    {
        $middlewares = is_array($name) ? $name : [$name => $middleware];

        foreach ($middlewares as $key => $group) {
            $this->router->middlewareGroup($key, $group);
        }
    }

    protected function routeMiddleware(string|array $name, string $middleware = null): void
    {
        $middlewares = is_array($name) ? $name : [$name => $middleware];

        foreach ($middlewares as $key => $class) {
            $this->router->aliasMiddleware($key, $class);
        }
    }

    /**
     * Register any crontab for this module.
     * @param Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule):void
    {

    }
    protected function registerPolicies(): void
    {
        foreach ($this->policies as $key => $value) {
            \Gate::policy($key, $value);
        }
    }

    protected function registerSchedule(): void
    {
        if ($this->app->runningInConsole()) {
            $this->app->booted(function () {
                $this->schedule($this->app->make(Schedule::class));
            });
        }
    }


    protected function loadViews(): void
    {
        $viewsPath = $this->modulePath('views');
        if (!is_dir($viewsPath)) {
            return;
        }
        $adminPath = $this->modulePath('views/admin');
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
        $defaultPath = $this->modulePath('views/default');
        if (is_dir($defaultPath)) {
            $this->loadViewsFrom($defaultPath, $this->uuid . ($hasTheme ? '_default' : ''));
        }
    }
    protected function registerProductTypes(): void
    {
        foreach ($this->productsTypes() as $productType) {
            $this->app['extension']->addProductType(app($productType));
        }
    }

    protected function productsTypes(): array
    {
        return [];
    }

    protected function routes():array
    {
        return [];
    }

}
