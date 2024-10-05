<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Requests;

use App\Models\Store\Basket\Basket;
use App\Services\Account\AccountEditService;
use Illuminate\Foundation\Http\FormRequest;

class ProcessCheckoutRequest extends FormRequest
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
        $types = app(\App\Services\Core\PaymentTypeService::class)->all()->keys()->implode(',');
        $rules = AccountEditService::rules($this->country ?? 'FR',false, false, auth()->id());
        if (setting('checkout_toslink', false)) {
            $rules['accept_tos'] = ['required', 'accepted'];
        }
        $rules['gateway'] = ['required', 'in:' . $types];
        return $rules;
    }

    protected function prepareForValidation()
    {
        if (Basket::getBasket()->total() == 0) {
            $this->merge(['gateway' => 'balance']);
        }
    }
}
