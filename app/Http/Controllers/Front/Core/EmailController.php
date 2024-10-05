<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Front\Core;

use App\Exceptions\WrongPaymentException;
use App\Helpers\Countries;
use App\Http\Controllers\Controller;
use App\Models\Account\EmailMessage;
use App\Models\Admin\EmailTemplate;
use App\Models\Core\Invoice;
use App\Models\Core\InvoiceItem;
use App\Models\Provisioning\Service;
use App\Services\Core\InvoiceService;
use App\Services\Store\GatewayService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmailController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->query('search');
        if ($search != null) {
            $emails = EmailMessage::where('recipient_id', auth()->user()->id)->where('subject', 'LIKE', "%{$search}%")->orderBy('id', 'DESC')->paginate(10);
            return view('front.client.emails.index', compact('emails', 'search'));
        }
        $emails = EmailMessage::where('recipient_id', auth()->user()->id)->orderBy('id', 'DESC')->orderBy('id', 'DESC')->paginate(10);
        return view('front.client.emails.index', compact('emails'));
    }

    public function show(EmailMessage $email)
    {
        if ($email->recipient_id != auth()->user()->id) {
            abort(404);
        }
        return new Response($email->content, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }
}
