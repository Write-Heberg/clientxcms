<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Front\Core;

use App\Helpers\Countries;
use App\Http\Controllers\Controller;
use App\Models\Account\EmailMessage;
use App\Models\Admin\EmailTemplate;
use App\Models\Core\Invoice;
use App\Models\Helpdesk\SupportTicket;
use App\Models\Provisioning\Service;
use App\Services\Store\GatewayService;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $servicesCount = auth()->user()->services()->count();
        $invoicesCount = auth()->user()->invoices()->where('status', '!=', 'draft')->count();
        $pending = auth()->user()->invoices()->where('status', Invoice::STATUS_PENDING)->count();
        $ticketsCount = auth()->user()->tickets()->count();
        $services = Service::where('customer_id', auth()->id())->orderBy('created_at', 'desc')->limit(5)->paginate();
        $tickets = SupportTicket::where('customer_id', auth()->id())->orderBy('created_at', 'desc')->limit(5)->paginate();
        $invoices = Invoice::where('customer_id', auth()->id())->where('status', '!=', Invoice::STATUS_DRAFT)->orderBy('created_at', 'desc')->limit(5)->paginate();
        $serviceFilters = Service::FILTERS;
        $invoiceFilters = Invoice::FILTERS;
        $gateways = GatewayService::getAvailable(1);
        return view('front.client.index', compact('gateways', 'tickets', 'services','services', 'invoices', 'ticketsCount', 'pending', 'servicesCount', 'invoicesCount', 'serviceFilters', 'invoiceFilters'));
    }
}
