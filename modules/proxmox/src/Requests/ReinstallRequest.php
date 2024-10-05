<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Modules\Proxmox\Requests;

use App\Rules\Hostname;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReinstallRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'template' => ['numeric','exists:proxmox_templates,id', Rule::requiredIf(function () {
                return $this->ose === null;
            })],
            'ose' => ['numeric','exists:proxmox_oses,id', Rule::requiredIf(function () {
                return $this->template === null;
            })],
            'password' => 'required|string|min:8|max:512',
            'sshkeys' => ['nullable', 'string', 'max:1000'],
            'hostname' => ['required', 'string', 'max:100', new Hostname()],
        ];
    }

}
