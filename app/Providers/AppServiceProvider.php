<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Providers;

use App\Core\License\LicenseGateway;
use App\Services\Core\SeoService;
use App\View\Components\BadgeStateComponant;
use App\View\Components\Provisioning\ServiceDaysRemaining;
use App\View\ThemeViewFinder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    const VERSION = "1.0.12-beta";
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('license', LicenseGateway::class);
        $this->app->singleton('seo', SeoService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        date_default_timezone_set('Europe/Paris');

        Builder::macro('whereLike', function (string $attribute, string $searchTerm) {
            return $this->orWhere($attribute, 'LIKE', "%{$searchTerm}%");
        });
        Paginator::defaultView('shared.pagination.default');
        Blade::component('badge-state', BadgeStateComponant::class);
        Blade::component('service-days-remaining', ServiceDaysRemaining::class);
        \View::share('clientxcms_version', self::VERSION);
        Carbon::setLocale(setting('app.locale', 'fr_FR'));
    }
}
