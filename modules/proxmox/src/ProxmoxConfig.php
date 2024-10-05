<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox;

use App\Abstracts\AbstractConfig;
use App\Models\Store\Product;
use App\Modules\Proxmox\Models\ProxmoxConfigModel;
use App\Modules\Proxmox\Models\ProxmoxOS;
use App\Modules\Proxmox\Models\ProxmoxTemplates;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Session;

class ProxmoxConfig extends AbstractConfig implements \App\Contracts\Store\ProductConfigInterface
{
    protected string $model = ProxmoxConfigModel::class;
    protected string $type = 'proxmox';

    public function render(Product $product)
    {
        $nodes = [];
        $storages = [];
        $bridges = [];
        try {
            foreach ($this->servers as $server) {
                foreach (ProxmoxAPI::fetchNodes($server) as $node) {
                    $nodes[$node] = $node;
                    foreach (ProxmoxAPI::fetchBridges($server, $node) as $bridge) {
                        $bridges[$bridge] = $bridge;
                    }
                }
                foreach (ProxmoxAPI::fetchStorages($server) as $storage) {
                    foreach ($storage as $_storage) {
                        $storages[$_storage] = $_storage;
                    }
                }
            }
            if (empty($nodes)) {
                Session::flash('error', __('proxmox::messages.clear_cache_if_empty'));
            }
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
        }
        $oses = ProxmoxOS::all()->pluck('name', 'id');
        $config = $this->getConfig($product->id, new ProxmoxConfigModel());
        $currentOses = $config->oses ?? [];
        $currentTemplates = $config->templates ?? [];
        $templates = ProxmoxTemplates::all()->pluck('name', 'id');
        return view('proxmox_admin::config', [
            'product' => $product,
            'config' => $config,
            'servers' => $this->servers->pluck('name', 'id'),
            'nodes' => $nodes,
            'oses' => $oses,
            'currentOses' => $currentOses,
            'templates' => $templates,
            'currentTemplates' => $currentTemplates,
            'storages' => $storages,
            'bridges' => $bridges,
            'types' => [
                ProxmoxConfigModel::TYPE_LXC => 'LXC',
                ProxmoxConfigModel::TYPE_QEMU => 'QEMU'
            ],
            'rates' => [
                "0.0125" => '100 MB/s',
                "31.25" => '250 MB/s',
                "62.5" => '500 MB/s',
                "125" => '1 GB/s',
                "1250" => '10 GB/s',
            ]
        ]);
    }

    public function validate(): array
    {
        return [
            'memory' => 'required|numeric|min:0.1',
            'disk' => 'required|numeric|min:0.1',
            'cores' => 'required|numeric|min:1',
            'sockets' => 'required|numeric|min:1',
            'type' => 'required|string|in:lxc,qemu',
            'storage' => 'required|string',
            'max_reinstall' => 'required|numeric|min:-1',
            'max_backups' => 'required|numeric|min:-1',
            'max_snapshots' => 'required|numeric|min:-1',
            'oses' => 'array|nullable',
            'templates' => 'array|nullable',
            'node' => 'required|string',
            'rate' => 'required|numeric|min:0.0125',
            'server_id' => 'required|numeric|exists:servers,id',
            'bridge' => 'required|string',
            'disk_storage' => 'required|string',
            'unprivileged' => 'boolean',
            'features' => 'nullable|string',
        ];
    }

    public function storeConfig(Product $product, array $parameters)
    {
        $parameters['oses'] = json_encode($parameters['oses'] ?? []);
        $parameters['templates'] = json_encode($parameters['templates'] ?? []);
        $parameters['unprivileged'] = $parameters['unprivileged'] ?? false;

        parent::storeConfig($product, $parameters);
    }

    public function updateConfig(Product $product, array $parameters)
    {
        $parameters['oses'] = json_encode($parameters['oses'] ?? []);
        $parameters['templates'] = json_encode($parameters['templates'] ?? []);
        $parameters['unprivileged'] = $parameters['unprivileged'] ?? false;

        parent::updateConfig($product, $parameters);
    }

    public function cloneConfig(Product $old, Product $new)
    {
        $config = $this->getConfig($old->id, new ProxmoxConfigModel());
        if ($config === null) {
            return;
        }
        $parameters = $config->toArray();
        unset($parameters['id']);
        unset($parameters['product_id']);
        unset($parameters['current_reinstall']);
        unset($parameters['current_backups']);
        unset($parameters['current_snapshots']);
        $parameters['product_id'] = $new->id;
        $this->storeConfig($new, $parameters);
    }



}
