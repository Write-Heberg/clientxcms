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
use App\Core\Menu\AdminMenuItem;
use App\Models\Account\Customer;
use App\Models\Core\Permission;
use App\Models\Provisioning\Service;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
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
        $this->registerMenuItems();
        if (is_installed()) {
            $this->registerAdminCountWidgets();
        }
    }

    private function registerMenuItems(){
        $this->app['extension']->addAdminMenuItem((new AdminMenuItem('dashboard', 'admin.dashboard', 'bi bi-speedometer2', 'admin.dashboard.title',1, Permission::ALLOWED)));
        $this->app['extension']->addAdminMenuItem((new AdminMenuItem('earn', 'admin.earn', 'bi bi-cash-coin', 'admin.dashboard.earn.title',2, 'admin.earn_page')));
        $this->app['extension']->addAdminMenuItem((new AdminMenuItem('customers', 'admin.customers.index', 'bi bi-people', 'admin.customers.title',3,'admin.show_customers')));
        $this->app['extension']->addAdminMenuItem((new AdminMenuItem('emails', 'admin.emails.index', 'bi bi-envelope', 'admin.emails.title',100,'admin.show_emails')));
    }

    private function registerAdminCountWidgets(){

        $date = setting('app_cron_last_run', null);
        if ($date == null){
            $date = __('admin.dashboard.tooltips.cron.never');
        }else{
            $date = Carbon::parse($date)->diffForHumans();
        }
        $cron = new AdminCountWidget('cron', 'bi bi-clock-history', 'admin.dashboard.widgets.cron', $date, 'admin.show_logs');
        $this->app['extension']->addAdminCountWidget($cron);
        $subDays = self::getSubDays();
        $users = Customer::where('is_deleted', false)->count();
        $usersWidgets = new AdminCountWidget('users', 'bi bi-people', 'admin.users.title', $users, 'admin.manage_customers');
        if ($subDays){
            $recentUsers = Customer::where('created_at', '>=', $subDays)->where('is_deleted', false)->count();
            $usersWidgets->tooltip = new AdminCountWidgetTooltips('customers',__('admin.dashboard.tooltips.users.label'), __('admin.dashboard.tooltips.users.new', ['count' => $recentUsers]), $recentUsers);
        }
        $this->app['extension']->addAdminCountWidget($usersWidgets);

        $this->app['extension']->addAdminCardsWidget(new AdminCardWidget('last_login', function(){
            $accounts = Customer::where('last_login', '!=', null)->orderBy('last_login', 'desc')->limit(3)->get();
            return view('admin.dashboard.cards.last-login', ['accounts' => $accounts]);
        }, "admin.dashboard_last_login",2));

        $this->app['extension']->addAdminCardsWidget(new AdminCardWidget('customer_search', function(){
            $fields = [
                'id' => 'User ID',
                'email' => __('global.email'),
                'first_name' => __('global.firstname'),
                'last_name' => __('global.lastname'),
                'phone' => __('global.phone'),
                'service_id' => 'Service ID',
                'invoice_id' => 'Invoice ID',
            ];
            return view('admin.dashboard.cards.customer-search', ['fields' => $fields]);
        }, "admin.manage_customers",1, 'services_canvas'));
        $this->app['settings']->addCardItem('security', 'roles', 'admin.roles.title', 'admin.roles.description', 'bi bi-person-badge', route('admin.roles.index'), 'admin.manage_roles');
    }

    public static function getSubDays():?Carbon {
        $request = request();
        if((int)$request->has('days')){
            if ($request->query('days') > 1 && $request->query('days') < 30) {
                return \Carbon\Carbon::now()->subDays($request->query('days'));
            }
        }
        return null;
    }
}
