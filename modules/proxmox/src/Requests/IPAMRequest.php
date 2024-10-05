<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox\Requests;

use Illuminate\Validation\Rule;

class IPAMRequest extends \Illuminate\Foundation\Http\FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ip' => ['required',Rule::unique('proxmox_ipam')->ignore($this->id),'string', function ($attribute, $value, $fail) {
                if ($value !== 'dhcp' && !filter_var($value, FILTER_VALIDATE_IP)) {
                    $fail("Le champ $attribute doit être une adresse IP valide ou la valeur 'dhcp'.");
                }
            }],
            'gateway' => 'required|ip',
            'netmask' => 'required|numeric',
            'bridge' => 'nullable|string',
            'mtu' => 'nullable|integer',
            'mac' => 'nullable|string',
            'ipv6' => 'nullable|ip',
            'ipv6_gateway' => 'nullable|ip',
            'is_primary' => 'nullable|boolean',
            'service_id' => 'nullable|integer',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:used,unavailable,available',
            'server' => 'nullable|string',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->get('server') == 'none') {
            $this->merge(['server' => null]);
        }
    }
}
