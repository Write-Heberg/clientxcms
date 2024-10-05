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
use App\Modules\Proxmox\Models\ProxmoxOS;
use App\Modules\Proxmox\Models\ProxmoxTemplates;
use App\Modules\Proxmox\ProxmoxAPI;
use App\Modules\Proxmox\Requests\OsesRequest;
use App\Modules\Proxmox\Requests\TemplatesRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class OsesController extends \App\Http\Controllers\Admin\AbstractCrudController
{
    protected string $model = \App\Modules\Proxmox\Models\ProxmoxOS::class;
    protected string $viewPath = 'proxmox_admin::oses';
    protected string $routePath = 'admin.proxmox.oses';
    protected string $translatePrefix = 'proxmox::messages.oses';
    protected bool $extensionPermission = true;


    public function getCreateParams()
    {
        $params = parent::getCreateParams();
        $params['servers'] = Server::where('type', 'proxmox')->get();
        $params['oses'] = [];
        try {
            foreach ($params['servers'] as $server) {
                $params['oses'][$server->id] = ProxmoxAPI::fetchOses($server);
            }
            if (empty($params['oses'])) {
                Session::flash('error', __('proxmox::messages.clear_cache_if_empty'));
            }
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
        }
        return $params;
    }

    public function store(OsesRequest $request)
    {
        $ose = ProxmoxOS::create($this->formatOses($request->validated()));
        return $this->storeRedirect($ose);
    }

    public function show(ProxmoxOS $ose) {
        $data = [];
        $data['item'] = $ose;
        $data['servers'] = Server::where('type', 'proxmox')->get();
        $data['osenames'] = (array) $ose->osnames ?? [];
        $data['osenames'] = array_map(function ($osnames) {
            return array_map(function ($node, $vmid) {
                return $node . '----' . $vmid;
            }, array_keys($osnames), $osnames);
        }, $data['osenames']);
        try {
            foreach ($data['servers'] as $server) {
                $data['oses'][$server->id] = ProxmoxAPI::fetchOses($server);
            }
            if (empty($data['oses'])) {
                Session::flash('error', __('proxmox::messages.clear_cache_if_empty'));
            }
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
        }
        return $this->showView($data);
    }

    public function update(OsesRequest $request, ProxmoxOS $ose)
    {
        $ose->update($this->formatOses($request->validated()));
        return $this->updateRedirect($ose);
    }

    public function destroy(ProxmoxOS $ose)
    {
        $ose->delete();
        return $this->deleteRedirect($ose);
    }

    protected function storeRedirect(Model $model)
    {
        event(new ResourceUpdatedEvent($model));
        return back()->with('success', __($this->flashs['created']));
    }

    private function formatOses(array $request)
    {
        foreach ($request['osnames'] as $server => $v) {
            $request['osnames'][$server] = [];
            foreach ($v as $vmid) {
                [$node, $vmid] = explode('----', $vmid);
                $request['osnames'][$server][$node] = $vmid;
            }
        }
        return $request;
    }

}

