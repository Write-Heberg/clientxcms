<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox;

use App\DTO\Store\ProductDataDTO;
use App\Models\Provisioning\Server;
use App\Modules\Proxmox\Models\ProxmoxConfigModel;
use App\Modules\Proxmox\Models\ProxmoxOS;
use App\Modules\Proxmox\Models\ProxmoxTemplates;
use App\Rules\Hostname;
use Illuminate\Validation\Rule;

class ProxmoxData extends \App\Abstracts\AbstractProductData implements \App\Contracts\Store\ProductDataInterface
{
    protected array $parameters = ['hostname', 'template', 'ose', 'password', 'sshkeys'];

    public function primary(\App\DTO\Store\ProductDataDTO $productDataDTO): string
    {
        return $productDataDTO->data['hostname'] ?? '';
    }
    public function render(\App\DTO\Store\ProductDataDTO $productDataDTO)
    {
        $storages = [];
        if ($productDataDTO->product->id != null) {
            $config = ProxmoxConfigModel::where('product_id', $productDataDTO->product->id)->first();
        } else {
            $templates = ProxmoxTemplates::all()->pluck('id');
            $oses = ProxmoxOS::all()->pluck('id');
            $config = new ProxmoxConfigModel(['oses' => $oses, 'templates' => $templates]);
            $servers = Server::where('type', 'proxmox')->where('status', 'active')->get();
            foreach ($servers as $server) {
                foreach (ProxmoxAPI::fetchStorages($server) as $storage) {
                    foreach ($storage as $_storage) {
                        $storages[$_storage] = $_storage;
                    }
                }
            }
        }
        if ($config === null) {
            return __('provisioning.product_not_configured');
        }
        $rates = [
            0.0125 => '100 MB/s',
            31.25 => '250 MB/s',
            62.5 => '500 MB/s',
            125 => '1 GB/s',
            1250 => '10 GB/s',
        ];
        $password = true;
        $sshkeys = false;
        $randomPassword = false;
        $templates = ProxmoxTemplates::whereIn('id', $config->templates)->get();
        $oses = ProxmoxOS::whereIn('id', $config->oses)->get();
        $data = $productDataDTO->data;
        $hostname = $data['hostname'] ?? "vps-" . rand(1000, 9999);
        $inAdmin = $productDataDTO->data['in_admin'] ?? false;
        if ($inAdmin){
            $inAdmin = !$productDataDTO->data['service_creation'] ?? true;
        }
        return view('proxmox::product-data', compact('storages', 'rates', 'config', 'hostname', 'inAdmin','config', 'data','oses', 'templates','password', 'sshkeys', 'randomPassword'));
    }

    public function parameters(ProductDataDTO $productDataDTO): array
    {
        $config = ProxmoxConfigModel::where('product_id', $productDataDTO->product->id)->first();
        if ($config === null) {
            return [];
        }
        $data = parent::parameters($productDataDTO);

        if (array_key_exists('template', $data)){
            $template = ProxmoxTemplates::find($data['template']);
            if ($template == null) {
                throw new \Exception('Proxmox Template not found');
            }
            $vmid = $template->vmids[$config->server_id][$config->node] ?? null;
            if ($vmid == null) {
                throw new \Exception('Proxmox VMID not found');
            }
            $data['vmid'] = $vmid;
        }
        if (array_key_exists('ose', $data)){
            $ose = ProxmoxOS::find($data['ose']);
            if ($ose == null) {
                throw new \Exception('Proxmox OS not found');
            }
            $os = $ose->osnames[$config->server_id][$config->node] ?? null;
            if ($os == null) {
                throw new \Exception('Proxmox VMID not found');
            }
            $data['osname'] = $os;
        }
        return $data;
    }

    public function validate(): array
    {
        return [
            'template' => ['numeric','exists:proxmox_templates,id', Rule::requiredIf(function () {
                return request()->input('ose') === null;
            })],
            'ose' => ['numeric','exists:proxmox_oses,id', Rule::requiredIf(function () {
                return request()->input('template') === null;
            })],
            'password' => 'required|string|min:8|max:512',
            'sshkeys' => ['nullable', 'string', 'max:1000'],
            'hostname' => ['required', 'string', 'max:100', new Hostname()],
        ];
    }


}
