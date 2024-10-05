<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox\Controllers;

use App\Events\Resources\ResourceUpdatedEvent;
use App\Models\Provisioning\Server;
use App\Modules\Proxmox\Models\ProxmoxTemplates;
use App\Modules\Proxmox\ProxmoxAPI;
use App\Modules\Proxmox\Requests\TemplatesRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class TemplatesController extends \App\Http\Controllers\Admin\AbstractCrudController
{
    protected string $model = \App\Modules\Proxmox\Models\ProxmoxTemplates::class;
    protected string $viewPath = 'proxmox_admin::templates';
    protected string $routePath = 'admin.proxmox.templates';
    protected string $translatePrefix = 'proxmox::messages.templates';
    protected bool $extensionPermission = true;

    public function getCreateParams()
    {
        $params = parent::getCreateParams();
        $params['servers'] = Server::where('type', 'proxmox')->get();
        $params['templates'] = [];
        try {
            foreach ($params['servers'] as $server) {
                $params['templates'][$server->id] = ProxmoxAPI::fetchTemplates($server);
            }
            if (empty($params['templates'])) {
                Session::flash('error', __('proxmox::messages.clear_cache_if_empty'));
            }
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
        }
        return $params;
    }

    public function store(TemplatesRequest $request)
    {
        $template = ProxmoxTemplates::create($this->formatVmids($request->validated()));
        return $this->storeRedirect($template);
    }

    public function show(ProxmoxTemplates $template) {
        $data = [];
        $data['item'] = $template;
        $data['servers'] = Server::where('type', 'proxmox')->get();
        $data['templates'] = [];
        $data['vmids'] = (array) $template->vmids ?? [];
        $data['vmids'] = array_map(function ($vmids) {
            return array_map(function ($node, $vmid) {
                return $node . '-' . $vmid;
            }, array_keys($vmids), $vmids);
        }, $data['vmids']);
        try {
            foreach ($data['servers'] as $server) {
                $data['templates'][$server->id] = ProxmoxAPI::fetchTemplates($server);
            }
            if (empty($data['templates'])) {
                Session::flash('error', __('proxmox::messages.clear_cache_if_empty'));
            }
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
        }
        return $this->showView($data);
    }

    public function update(TemplatesRequest $request, ProxmoxTemplates $template)
    {
        $template->update($this->formatVmids($request->validated()));
        return $this->updateRedirect($template);
    }

    public function destroy(ProxmoxTemplates $template)
    {
        $template->delete();
        return $this->deleteRedirect($template);
    }

    protected function storeRedirect(Model $model)
    {
        event(new ResourceUpdatedEvent($model));
        return back()->with('success', __($this->flashs['created']));
    }
    private function formatVmids(array $request)
    {
        foreach ($request['vmids'] as $server => $v) {
            $request['vmids'][$server] = [];
            foreach ($v as $vmid) {
                [$node, $vmid] = explode('-', $vmid);
                $request['vmids'][$server][$node] = $vmid;
            }
        }
        return $request;
    }
}

