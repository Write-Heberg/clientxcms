<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Front\Core\Helpdesk;

use App\Http\Controllers\Controller;
use App\Http\Requests\Helpdesk\ReplyTicketRequest;
use App\Http\Requests\Helpdesk\SubmitTicketRequest;
use App\Models\Helpdesk\SupportDepartment;
use App\Models\Helpdesk\SupportTicket;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('filter')) {
            $filter = $request->get('filter');
            if (!in_array($filter, array_keys(SupportTicket::FILTERS))){
                return redirect()->route('front.support.index');
            }
            $tickets = SupportTicket::where('customer_id', auth()->id())->where('status', $request->get('filter'))->orderBy('created_at', 'desc')->paginate(10);
        } else {
            $filter = null;
            $tickets = SupportTicket::where('customer_id', auth()->id())->orderBy('created_at', 'desc')->paginate(10);
        }
        $filters = SupportTicket::FILTERS;
        return view('front.client.helpdesk.support.index', compact('tickets', 'filter', 'filters'));
    }

    public function create(Request $request)
    {
        $departments = SupportDepartment::all();
        $priorities = SupportTicket::getPriorities();
        $related = auth()->user()->supportRelatedItems();
        $currentdepartment = $request->query('department') ?? null;
        if ($currentdepartment) {
            if (!$departments->contains('id', $currentdepartment)) {
                return redirect()->route('front.support.create');
            }
        } else {
            $currentdepartment = $departments->first()->id ?? null;
        }
        return view('front.client.helpdesk.support.create', ['departments' => $departments, 'priorities' => $priorities, 'related' => $related, 'currentdepartment' => $currentdepartment]);
    }

    public function store(SubmitTicketRequest $request)
    {
        $ticket = new SupportTicket();
        $ticket->fill($request->only(['department_id', 'priority', 'subject', 'related_id', 'related_type']));
        $ticket->customer_id = auth()->id();
        $ticket->status = SupportTicket::STATUS_OPEN;
        $ticket->save();
        $ticket->addMessage($request->get('content'), auth()->id());
        foreach ($request->file('attachments', []) as $attachment) {
            $ticket->addAttachment($attachment, auth('web')->id());
        }
        return redirect()->route('front.support.index');
    }

    public function show(SupportTicket $ticket)
    {
        abort_if($ticket->customer_id != auth()->id(), 404);
        return view('front.client.helpdesk.support.show', compact('ticket'));
    }

    public function close(SupportTicket $ticket)
    {
        abort_if($ticket->customer_id != auth()->id(), 404);
        $ticket->close();
        return redirect()->route('front.support.index')->with('success', __('client.support.ticket_closed'));
    }

    public function reopen(SupportTicket $ticket)
    {
        abort_if($ticket->customer_id != auth()->id(), 404);
        $days = setting('support_ticket_reopen_days', 7);
        if ($ticket->closed_at->diffInDays(now()) > $days && $days > 0) {
            return back()->with('error', __('client.support.ticket_reopen_days', ['days' => $days]));
        }
        $ticket->reopen();
        return redirect()->route('front.support.index')->with('success', __('client.support.ticket_reopened'));
    }

    public function reply(ReplyTicketRequest $request, SupportTicket $ticket)
    {
        abort_if($ticket->customer_id != auth()->id(), 404);
        if ($ticket->isClosed()) {
            return back()->with('error', __('client.support.ticket_closed_reply'));
        }
        $ticket->reply($request->get('content'));
        foreach ($request->file('attachments', []) as $attachment) {
            $ticket->addAttachment($attachment, auth('web')->id());
        }
        if (array_key_exists('close', $request->all())) {
            $ticket->close();
            return redirect()->route('front.support.index')->with('success', __('client.support.ticket_closed'));
        }
        return back()->with('success', __('client.support.ticket_replied'));
    }

    public function download(SupportTicket $ticket, $attachment)
    {
        $attachment = $ticket->attachments()->where('id', $attachment)->first();
        abort_if(!$attachment, 404);
        abort_if($attachment->customer_id != auth('web')->id(), 404);
        return response()->download(storage_path("app/". $attachment->path), $attachment->name);
    }
}
