<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Requests\Provisioning;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServerRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $types = app('extension')->getProductTypes()->filter(function($k) { return $k->server() != null; })->map(function ($k) {
            return $k->uuid();
        });
        return [
            'name' => ['string', 'max:255', Rule::unique('servers', 'name')->ignore($this->id)],
            'ip' => ['string', 'max:255', Rule::unique('servers', 'ip')->ignore($this->id)],
            'port' => ['numeric', 'min:1', 'max:65535'],
            'username' => ['string'],
            'password' => ['string'],
            'status' => ['string', Rule::in(['active', 'hidden', 'unreferenced'])],
            'type' => ['string', Rule::in($types)],
            'hostname' => ['string', 'required'],
            'address' => ['string', 'required'],
            'maxaccounts' => ['numeric', 'min:0', 'nullable'],
        ];
    }
}
