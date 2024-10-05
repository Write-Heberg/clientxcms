<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin\Provisioning;

use App\Http\Controllers\Admin\AbstractCrudController;
use App\Http\Requests\Provisioning\StoreServerRequest;
use App\Http\Requests\Provisioning\UpdateServerRequest;
use App\Models\Provisioning\Server;
use DB;
use Illuminate\Http\Request;

class ServerController extends AbstractCrudController
{
    protected string $model = 'App\Models\Provisioning\Server';
    protected string $routePath = 'admin.servers';
    protected string $viewPath = 'admin.provisioning.servers';
    protected string $translatePrefix = 'admin.servers';
    protected ?string $managedPermission = 'admin.manage_servers';

    protected array $labels = [
        "pterodactyl" => ["Client API", "Application API"],
        "wisp" => ["Client API", "Application API"],
        "plesk" => ["Username", "Password"],
        "virtualizor" => ["Key", "Password"],
        "virtualizorcloud" => ["Key", "Password"],
        "proxmox" => ["Token Id", "Secret"],
        "cpanel" => ["Username", "Password"],
    ];

    public function config() {
        $this->checkPermission('create');
        return $this->index(request());
    }
    public function index(Request $request)
    {
        $this->checkPermission('showAny');
        $items = $this->model::orderBy('created_at', 'desc')->paginate($this->perPage);
        return view($this->viewPath . '.index', $this->getIndexParams($items, $this->translatePrefix ?? $this->viewPath));
    }

    public function show(Server $server)
    {
        $this->checkPermission('show', $server);
        $params['item'] = $server;
        $params['types'] = app('extension')->getProductTypes()->filter(function($k) { return $k->server() != null; })->mapWithKeys(function ($k, $v) {
            return [$k->uuid() => $k->title()];
        });
        $params['labels'] = $this->labels;
        return $this->showView($params);
    }

    public function create(Request $request)
    {
        $this->checkPermission('create');
        $params['types'] = app('extension')->getProductTypes()->filter(function($k) { return $k->server() != null; })->mapWithKeys(function ($k, $v) {
            return [$k->uuid() => $k->title()];
        });
        $params['labels'] = $this->labels;
        return $this->createView($params);
    }

    public function store(StoreServerRequest $request)
    {
        $this->checkPermission('create');
        $data = $request->only(['name', 'address', 'port', 'type', 'username', 'password', 'hostname', 'maxaccounts', 'status']);
        $server = new Server();
        $server->fill($data);
        $server->save();
        return $this->storeRedirect($server);
    }

    public function update(UpdateServerRequest $request)
    {
        $this->checkPermission('update');
        $data = $request->only(['name', 'address', 'port', 'type', 'username', 'password', 'hostname', 'maxaccounts', 'status']);
        $server = Server::findOrFail($request->id);
        $server->fill($data);
        $server->save();
        return $this->updateRedirect($server);
    }

    public function test(Request $request)
    {
        $this->checkPermission('create');
        $data = $request->only(['address', 'port', 'type', 'username', 'password', 'hostname']);
        $server = new Server();
        $server->fill($data);
        $type = $data['type'] ?? 'none';
        $serverType = app('extension')->getProductTypes()->filter(function($k) use ($type) { return $k->server() != null && $k->server()->uuid() == $type; })->first();
        if ($serverType == null) {
            return response()->json(['success' => false, 'message' => 'Server type not found']);
        }
        try {
            $validator = \Validator::make($data, $serverType->server()->validate());
            if ($validator->fails()) {
                $errors = collect($validator->errors())->map(function ($item) {
                    return $item[0];
                })->implode(', ');
                return response()->json(['success' => false, 'status' => 500, 'message' => $errors]);
            }
            $result = $serverType->server()->testConnection($server->toArray());
            if ($result->successful()) {
                return response()->json(['success' => true, 'status' => $result->status(), 'message' => 'Connection successful']);
            }
            return response()->json(['success' => false,'status' => $result->status(), 'message' => $result->toString()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'status' => 500, 'message' => $e->getMessage()]);
        }

    }

    public function destroy(Server $server)
    {
        $this->checkPermission('delete', $server);
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $server->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        return $this->deleteRedirect($server);
    }
}
