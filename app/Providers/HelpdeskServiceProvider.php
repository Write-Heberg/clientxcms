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
use App\Http\Controllers\Admin\Helpdesk\HelpdeskSettingController;
use App\Http\Controllers\Admin\Helpdesk\Support\DepartmentController;
use App\Models\Core\Permission;
use App\Models\Helpdesk\SupportTicket;
use Illuminate\Database\QueryException;
use Illuminate\Support\ServiceProvider;

class HelpdeskServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (!is_installed()) {
            return;
        }
        try {
            $this->registerWidgets();
        } catch (QueryException $e){
            // Do nothing
        }
    }

    private function registerWidgets()
    {
        $tickets = SupportTicket::where('status', 'open')->count();
        $subDays = AdminServiceProvider::getSubDays();
        $ticketWidgets = new AdminCountWidget('tickets', 'bi bi-chat-left-text', 'admin.helpdesk.widgets.tickets', $tickets, 'admin.manage_tickets');
        if ($subDays) {
            $recentTickets = SupportTicket::where('status', 'open')->where('created_at', '>=', $subDays)->select('id')->count();
            $ticketWidgets->tooltip = new AdminCountWidgetTooltips('tickets', __('admin.dashboard.tooltips.tickets.label'), __('admin.dashboard.tooltips.tickets.new', ['count' => $recentTickets]), $recentTickets);
        }
        $this->app['extension']->addAdminCountWidget($ticketWidgets);
        $this->app['extension']->addAdminCardsWidget(new AdminCardWidget('support', function(){
            $tickets = SupportTicket::where('status', 'open')->limit(3)->get();
            return view('admin.helpdesk.cards.tickets', ['tickets' => $tickets]);
        }, "admin.manage_tickets", 2));
        $this->app['settings']->addCard('helpdesk', 'admin.helpdesk.title', 'admin.helpdesk.description', 4);
        $this->app['settings']->addCardItem('helpdesk', 'helpdesk_settings', 'admin.helpdesk.settings.title', 'admin.helpdesk.settings.description', 'bi bi-gear', [HelpdeskSettingController::class, 'showSettings'], Permission::MANAGE_SETTINGS);
        $this->app['settings']->addCardItem('helpdesk', 'helpdesk_departments', 'admin.helpdesk.departments.title', 'admin.helpdesk.departments.description', 'bi bi-building', action([DepartmentController::class, 'index']), 'admin.manage_departments');
        $this->app['extension']->addAdminMenuItem(new AdminMenuItem('helpdesk', 'admin.helpdesk.tickets.index', 'bi bi-chat-left-text', 'admin.helpdesk.title', 6, "admin.reply_tickets"));
    }

}
