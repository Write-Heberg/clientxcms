<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin;

use App\Core\Admin\Dashboard\AdminCardWidget;
use App\Core\Admin\Dashboard\AdminCountWidget;
use App\Core\Admin\Dashboard\DashboardLayoutManager;
use App\DTO\Admin\Dashboard\BestSellingProductsDTO;
use App\DTO\Admin\Dashboard\Earn\EarnStatisticsItemDTO;
use App\DTO\Admin\Dashboard\Earn\MonthCanvasDTO;
use App\Models\Account\Customer;
use App\Models\Core\Invoice;
use App\Models\Provisioning\Service;
use App\Models\Provisioning\ServiceRenewals;
use App\Models\Store\Product;

class DashboardController
{
    public function index()
    {
        $cards = app('extension')->getAdminCardsWidgets();
        $widgets = app('extension')->getAdminCountWidgets();

        $cards = collect($cards)->filter(function (AdminCardWidget $card) {
            return auth('admin')->user()->can($card->permission);
        });
        $widgets = collect($widgets)->filter(function (AdminCountWidget $widget) {
            return auth('admin')->user()->can($widget->permission);
        });
        $frozen = file_exists(storage_path('frozen'));
        $notification_error = \Cache::get('notification_error');
        $data = [
            'widgets' => app('extension')->getAdminCountWidgets(),
            'cards' => $cards,
            'in_debug' => config('app.debug'),
            'frozen' => $frozen,
            'notification_error' => $notification_error,
        ];
        return view('admin.dashboard.dashboard', $data);
    }

    public function earn()
    {
        staff_aborts_permission('admin.earn_page');
        $start = now()->subYears(30);
        $end = now();
        $widgets = [];
        $widgets[__('admin.dashboard.earn.all_time')] = $this->makeWidgets($start, $end);
        $widgets[__('admin.dashboard.earn.current_month')] = $this->makeWidgets(now()->startOfMonth(), $end);
        $widgets[__('admin.dashboard.earn.last_30_days')] = $this->makeWidgets(now()->subDays(30), $end);
        $widgets[__('admin.dashboard.earn.last_7_days')] = $this->makeWidgets(now()->subDays(7), $end);
        $widgets[__('admin.dashboard.earn.today')] = $this->makeWidgets(now()->subDays(1), $end);
        return view('admin.dashboard.earn', [
            'widgets' => $widgets,
            'bestSelling' => $this->bestSellingProducts(),
            'gateways' => BestSellingProductsDTO::getGateways(),
            'services' => $this->getServices(),
            'months' => $this->getMonths(),
            'lastorders' => Invoice::where('status', 'paid')->orderBy('paid_at', 'desc')->limit(5)->get(),
        ]);
    }


    private function getMonths()
    {
        $months = collect();
        for ($i = 1; $i <= 12; $i++) {
            $months->push([
                'month' => now()->month($i)->format('F'),
                'total' => Invoice::where('paid_at', '>', now()->month($i)->startOfMonth())
                    ->where('paid_at', '<', now()->month($i)->endOfMonth())
                    ->where('status', 'paid')->sum('total')
            ]);
        }
        return new MonthCanvasDTO($months);
    }
    private function getServices()
    {
        return [
            'expires_soon' => Service::where('status', 'active')->where('expires_at', '<=', now()->addDays(setting('core.services.days_before_expiration', 7)))->count(),
            'total' => Service::where('expires_at', '>', now()->startOfMonth())->where('status', 'active')->count(),
            'already_renewed' => ServiceRenewals::where('renewed_at', '>', now()->startOfMonth())->count(),
        ];
    }

    private function makeWidgets(\DateTime $start, ?\DateTime $end)
    {
        $total = Invoice::whereBetween('paid_at', [$start, $end])->where('status', 'paid')->sum('total');
        $tax = Invoice::whereBetween('paid_at', [$start, $end])->where('status', 'paid')->sum('tax');
        $fees = Invoice::whereBetween('paid_at', [$start, $end])->where('status', 'paid')->sum('fees');
        $ca = $total - $tax - $fees;
        $invoices = Invoice::whereBetween('paid_at', [$start, $end])->where('status', 'paid')->count();
        $services = Service::whereBetween('created_at', [$start, $end])->where('status', 'active')->count();
        $widgets = [
            new EarnStatisticsItemDTO('bi bi-wallet2', __('admin.dashboard.earn.total_earned'), formatted_price($total), 'primary'),
            new EarnStatisticsItemDTO('bi bi-cash-coin', __('admin.dashboard.earn.total_cash'), formatted_price($ca), 'success'),
            new EarnStatisticsItemDTO('bi bi-currency-dollar', __('admin.dashboard.earn.total_tax'), formatted_price($tax), 'danger'),
            new EarnStatisticsItemDTO('bi bi-tag', __('admin.dashboard.earn.total_fees'), formatted_price($fees),  'info'),
            new EarnStatisticsItemDTO('bi bi-receipt-cutoff', __('admin.dashboard.earn.total_invoices'), $invoices,  'warning'),
            new EarnStatisticsItemDTO('bi bi-boxes', __('admin.dashboard.earn.total_services'), $services,'secondary')
        ];
        return $widgets;
    }

    private function bestSellingProducts()
    {
        $dto = \App\DTO\Admin\Dashboard\BestSellingProductsDTO::getBestProducts();
        $week = \App\DTO\Admin\Dashboard\BestSellingProductsDTO::getBestProductsLastWeek();
        $month = \App\DTO\Admin\Dashboard\BestSellingProductsDTO::getBestProductsLastMonth();
        $products = BestSellingProductsDTO::getDetailedProducts();
        $productsNames = Product::whereIn('id', $products->pluck('related_id'))->get()->pluck('name', 'id')->toArray();
        return [
            'dto' => $dto,
            'week' => $week,
            'month' => $month,
            'productsNames' => $productsNames,
            'products' => $products,
            'split' => intdiv($products->count(), 2),
        ];
    }
}
