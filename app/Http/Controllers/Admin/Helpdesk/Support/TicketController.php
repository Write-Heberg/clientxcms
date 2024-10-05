<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin\Helpdesk\Support;

use App\Http\Requests\Helpdesk\ReplyTicketRequest;
use App\Http\Requests\Helpdesk\SubmitTicketRequest;
use App\Models\Account\Customer;
use App\Models\Helpdesk\SupportTicket;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class TicketController extends \App\Http\Controllers\Admin\AbstractCrudController
{
    protected string $viewPath = 'admin.helpdesk.tickets';
    protected string $routePath = 'admin.helpdesk.tickets';
    protected string $model = \App\Models\Helpdesk\SupportTicket::class;

    protected string $searchField = 'subject';
    protected string $filterField = 'status';

    public function getIndexFilters(): array
    {
        return collect(SupportTicket::FILTERS)->mapWithKeys(function ($k, $v) {
            return [$k => __('global.states.' . $v)];
        })->toArray();
    }

    protected function queryIndex(): LengthAwarePaginator
    {
        $departments = collect(\App\Models\Helpdesk\SupportDepartment::all())->pluck('id')->toArray();
        $priorities = array_keys(SupportTicket::PRIORITIES);
        if (\request()->query->has('filter')){
            $filter = \request()->query('filter');
            if (in_array($filter, $departments)){
                return $this->model::where('department_id', $filter)->orderBy('created_at', 'desc')->paginate($this->perPage);
            }
            if (in_array($filter, $priorities)){
                return $this->model::where('priority', $filter)->orderBy('created_at', 'desc')->paginate($this->perPage);
            }
        }
        return parent::queryIndex();
    }

    protected function filterIndex(string $filter)
    {
        $departments = collect(\App\Models\Helpdesk\SupportDepartment::all())->pluck('id')->toArray();
        $priorities = array_keys(SupportTicket::PRIORITIES);

        if (in_array($filter, $departments)){
            return $this->model::where('department_id', $filter)->orderBy('created_at', 'desc')->paginate($this->perPage);
        }
        if (in_array($filter, $priorities)){
            return $this->model::where('priority', $filter)->orderBy('created_at', 'desc')->paginate($this->perPage);
        }
        return parent::filterIndex($filter);
    }

    public function getCreateParams()
    {
        $data = parent::getCreateParams();
        $data['departments'] = \App\Models\Helpdesk\SupportDepartment::all();
        if (\request()->query->has('department_id')){
            $data['currentdepartment'] = \request()->query('department_id');
        } else {
            $data['currentdepartment'] = $data['departments']->first()->id ?? null;
        }
        $customerId = \request()->query('customer_id');
        $departmentId = \request()->query('department_id');
        if ($data['departments']->contains('id', $departmentId)){
            $data['currentdepartment'] = $departmentId;
        }
        $customer = Customer::find($customerId);
        if ($customerId){
            if ($customer == null){
                $data['related'] = [];
                \Session::flash('error', __('admin.helpdesk.tickets.customer_not_found'));
            } else {
                $data['related'] = $customer->supportRelatedItems();
            }
            $data['priorities'] = SupportTicket::getPriorities();
        } else {
            $data['customers'] = $this->customers();
        }
        $data['hasCustomer'] = \request()->query->get('customer_id', 0);
        return $data;
    }

    public function show(SupportTicket $ticket)
    {
        $this->checkPermission('show', $ticket);
        $data['ticket'] = $ticket;
        $data['item'] = $ticket;
        $data['related'] = $ticket->customer->supportRelatedItems();
        $data['priorities'] = SupportTicket::getPriorities();
        $data['departments'] = \App\Models\Helpdesk\SupportDepartment::all()->pluck('name', 'id')->toArray();
        return $this->showView($data);
    }

    public function destroy(SupportTicket $ticket)
    {
        $this->checkPermission('delete', $ticket);
        if ($ticket->isClosed()){
            try {
                foreach ($ticket->attachments as $attachment) {
                    \File::delete(storage_path("app/". $attachment->path));
                }
                \File::deleteDirectory(storage_path("app/helpdesk/attachments/{$ticket->id}"));
            } catch (\Exception $e) {
                logger()->error($e->getMessage());
            }
            $ticket->attachments()->delete();
            $ticket->delete();
            return $this->deleteRedirect($ticket);
        }
        $ticket->close(true);
        return redirect()->route($this->routePath . '.index')->with('success', __('client.support.ticket_closed'));
    }

    public function reply(ReplyTicketRequest $request, SupportTicket $ticket)
    {
        $this->checkPermission('reply', $ticket);
        $ticket->addMessage($request->get('content'), null, auth('admin')->id());
        $ticket->reply($request->get('content'));
        foreach ($request->file('attachments', []) as $attachment) {
            $ticket->addAttachment($attachment, null, auth('admin')->id());
        }
        if ($request->has('close')){
            $ticket->close();
            return redirect()->route($this->routePath . '.index')->with('success', __('client.support.ticket_closed'));
        }
        return redirect()->route($this->routePath . '.show', $ticket)->with('success', __('client.support.ticket_replied'));
    }

    public function close(SupportTicket $ticket)
    {
        return $this->destroy($ticket);
    }


    protected function getSearchFields()
    {
        return [
            'id' => "Identifier",
            'customer.email' => __('global.customer'),
            'subject' => __( 'client.support.subject'),
        ];
    }

    public function reopen(SupportTicket $ticket)
    {
        $this->checkPermission('update', $ticket);
        $ticket->reopen();
        return redirect()->route($this->routePath . '.show', $ticket)->with('success', __('client.support.ticket_reopened'));
    }

    public function update(Request $request, SupportTicket $ticket)
    {
        $this->checkPermission('update', $ticket);
        $validated = $request->validate([
            'department_id' => 'required|exists:support_departments,id',
            'priority' => 'required|in:' . implode(',', array_keys(SupportTicket::PRIORITIES)),
            'subject' => 'required|string',
        ]);
        if ($request->get('related_id') == 'none'){
            $validated['related_id'] = null;
            $validated['related_type'] = null;
        } else {
            [$relatedType, $relatedId] = explode('-', $request->get('related_id'));
            $validated['related_id'] = $relatedId;
            $validated['related_type'] = $relatedType;
        }
        $ticket->update($validated);
        return redirect()->route($this->routePath . '.show', $ticket)->with('success', __('global.updated'));
    }


    private function customers()
    {
        return Customer::select(['id', 'email', 'firstname', 'lastname'])->get()->mapWithKeys(function(Customer $customer) {
            return [$customer->id => $customer->email];
        });
    }

    public function store(SubmitTicketRequest $request)
    {
        $this->checkPermission('create');
        $validated = $request->validated();
        if ($request->query->has('customer_id') && !Customer::find($request->query->get('customer_id'))){
            return redirect()->route($this->routePath . '.create')->with('error', __('admin.helpdesk.tickets.customer_not_found'));
        }
        $validated['customer_id'] = $request->query->get('customer_id');
        $ticket = SupportTicket::create($validated);
        $ticket->addMessage($validated['content'], null, auth('admin')->id());
        foreach ($request->file('attachments', []) as $attachment) {
            $ticket->addAttachment($attachment, null, auth('web')->id());
        }
        return redirect()->route($this->routePath . '.show', $ticket)->with('success', __('global.created'));
    }

    public function download(SupportTicket $ticket, $attachment)
    {
        $this->checkPermission('show', $ticket);
        $attachment = $ticket->attachments()->where('id', $attachment)->first();
        abort_if(!$attachment, 404);
        return response()->download(storage_path("app/{$attachment->path}"), $attachment->name);
    }

    protected function getPermissions(string $tablename)
    {
        $tablename = "tickets";
        return [
            'showAny' => [
                'admin.manage_' . $tablename,
            ],
            'show' => [
                'admin.manage_' . $tablename,
            ],
            'update' => [
                'admin.manage_' . $tablename,
            ],
            'delete' => [
                'admin.close_' . $tablename,
            ],
            'create' => [
                'admin.create_' . $tablename,
            ],
            'reply' => [
                'admin.reply_' . $tablename,
            ],
        ];
    }
}
