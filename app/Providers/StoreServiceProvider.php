<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Providers;

use App\Core\Admin\Dashboard\AdminCardWidget;
use App\Core\Menu\AdminMenuItem;
use App\Http\Controllers\Admin\Store\CouponController;
use App\Http\Controllers\Admin\Store\GatewayController;
use App\Http\Controllers\Admin\Store\GroupController;
use App\Http\Controllers\Admin\Store\ProductController;
use App\Http\Controllers\Admin\Store\Settings\SettingsStoreController;
use App\Models\Core\Gateway;
use App\Models\Core\Permission;
use App\Services\SettingsService;
use App\Core\Admin\Dashboard\AdminCountWidget;
use App\Core\Admin\Dashboard\AdminCountWidgetTooltips;
use App\Models\Core\Invoice;
use App\Services\Store\CurrencyService;
use App\Services\Store\ProductTypeService;
use App\Services\Store\RecurringService;
use Illuminate\Support\ServiceProvider;

class StoreServiceProvider extends ServiceProvider
{

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(RecurringService::class);
        $this->app->singleton(CurrencyService::class);
        $this->app->singleton(ProductTypeService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (!is_installed()) {
            return;
        }
        $gateways = Gateway::all();
        /** @var SettingsService $setting */
        $setting = app('settings');
        $setting->addCard('store', 'admin.settings.store.title', 'admin.settings.store.description', 1);
        $setting->addCardItem('store', 'product', 'admin.products.title', 'admin.products.description', 'bi bi-box', action([ProductController::class, 'index']), 'admin.manage_products');
        $setting->addCardItem('store', 'group', 'admin.groups.title', 'admin.groups.description', 'bi bi-shop', action([GroupController::class, 'index']), 'admin.manage_groups');
        $setting->addCardItem('store', 'billing', 'admin.settings.store.checkout.title', 'admin.settings.store.checkout.description', 'bi bi-basket2-fill', [SettingsStoreController::class, 'showBilling'], Permission::MANAGE_SETTINGS);
        $setting->addCardItem('store', 'coupon', 'coupon.coupons', 'coupon.admin.description', 'bi bi-percent', action([CouponController::class, 'index']), 'admin.manage_coupons');

        foreach ($gateways as $gateway) {
            if ($gateway->uuid == 'none') {
                continue;
            }
            $setting->addCardItem('store', $gateway->uuid, $gateway->name, 'admin.settings.store.gateways.description', $gateway->paymentType()->icon(), [GatewayController::class, 'config'], 'admin.manage_gateways');
        }

        $invoices = Invoice::count();
        $subDays = AdminServiceProvider::getSubDays();
        $invoiceWidgets = new AdminCountWidget('invoices', 'bi bi-receipt-cutoff','admin.invoices.title', $invoices, 'admin.manage_invoices');
        if ($subDays){
            $recentInvoices = Invoice::where('created_at', '>=', $subDays)->where('status', 'paid')->select('id')->count();
            $invoiceWidgets->tooltip = new AdminCountWidgetTooltips('customers',__('admin.dashboard.tooltips.invoices.label'), __('admin.dashboard.tooltips.invoices.paied', ['count' => $recentInvoices]));
        }
        $this->app['extension']->addAdminCountWidget($invoiceWidgets);
        $this->app['extension']->addAdminMenuItem((new AdminMenuItem('services', 'admin.services.index', 'bi bi-box2', 'admin.services.title',3,'admin.show_services')));
        $this->app['extension']->addAdminMenuItem((new AdminMenuItem('invoices', 'admin.invoices.index', 'bi bi-receipt-cutoff', 'admin.invoices.title',4,'admin.show_invoices')));
        $this->app['extension']->addAdminCardsWidget(new AdminCardWidget('best_products', function(){
            $dto = \App\DTO\Admin\Dashboard\BestSellingProductsDTO::getBestProducts();
            $week = \App\DTO\Admin\Dashboard\BestSellingProductsDTO::getBestProductsLastWeek();
            $month = \App\DTO\Admin\Dashboard\BestSellingProductsDTO::getBestProductsLastMonth();
            return view('admin.dashboard.cards.best-selling', compact('dto', 'week', 'month'));
        }, "admin.earn_page", 2));
    }

}
