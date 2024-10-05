<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Providers;

use App\Core\Admin\Dashboard\AdminCardWidget;
use App\Core\Admin\Dashboard\AdminCountWidget;
use App\Core\Admin\Dashboard\AdminCountWidgetTooltips;
use App\Http\Controllers\Admin\Provisioning\ServerController;
use App\Http\Controllers\Admin\Store\GatewayController;
use App\Models\Provisioning\Service;
use App\Services\SettingsService;
use Illuminate\Support\ServiceProvider;

class ProvisionningServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (!is_installed()) {
            return;
        }
        $this->registerWidgets();
    }

    private function registerWidgets(){

        $customers = Service::countCustomers();
        $subDays = AdminServiceProvider::getSubDays();
        $customerWidgets = new AdminCountWidget('customers', 'bi bi-person-vcard','admin.customers.title',  $customers, 'admin.manage_customers');
        if ($subDays){
            $recentCustomers = Service::groupBy('customer_id')->where('created_at', '>=', $subDays)->select('id')->count();
            $customerWidgets->tooltip = new AdminCountWidgetTooltips('customers',__('admin.dashboard.tooltips.customers.label'), __('admin.dashboard.tooltips.customers.new', ['count' => $recentCustomers]), $recentCustomers);
        }
        $this->app['extension']->addAdminCountWidget($customerWidgets);
        $services = Service::where('status', 'active')->count();
        $subDays = AdminServiceProvider::getSubDays();
        $serviceWidgets = new AdminCountWidget('services', 'bi bi-boxes','admin.services.title', $services, 'admin.manage_services');
        if ($subDays) {
            $recentServices = Service::where('status', 'active')->where('created_at', '>=', $subDays)->select('id')->count();
            $serviceWidgets->tooltip = new AdminCountWidgetTooltips('services',__('admin.dashboard.tooltips.services.label'), __('admin.dashboard.tooltips.services.new', ['count' => $recentServices]), $recentServices);
        }
        $this->app['extension']->addAdminCountWidget($serviceWidgets);

        $this->app['extension']->addAdminCardsWidget(new AdminCardWidget('services_canvas', function(){
            $data = Service::selectRaw('count(*) as count, status')->groupBy('status')->get();
            $dto = new \App\DTO\Admin\Dashboard\ServiceStatesCanvaDTO($data->toArray());
            return view('admin.dashboard.cards.services-canvas', ['dto' => $dto]);
        }, 'admin.show_services', 1));
        $this->app['extension']->addAdminCardsWidget(new AdminCardWidget('services', function(){
            $services = Service::where('status', 'active')->where('expires_at', '<=', \Carbon\Carbon::now()->addDays(setting('core.services.days_before_expiration', 7)))->limit(3)->get();
            return view('admin.dashboard.cards.services-expiration', ['services' => $services]);
        }, 'admin.show_services', 2));

        /** @var SettingsService $setting */
        $setting = app('settings');
        $setting->addCardItem('core', 'servers', 'admin.servers.title', 'admin.servers.subheading', 'bi bi-hdd-rack', route('admin.servers.index'), "admin.manage_servers");
        $setting->addCardItem('core', 'subdomains', 'admin.subdomains.title', 'admin.subdomains.subheading', 'bi bi-list-stars', route('admin.subdomains.index'), true);

    }
}
