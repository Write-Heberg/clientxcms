<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Requests\Provisioning;

use App\Models\Provisioning\Service;
use App\Services\Store\RecurringService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $types = app('extension')->getProductTypes()->keys()->merge(['none'])->toArray();
        $billing = app(RecurringService::class)->getRecurringTypes();
        return [
            'name' => ['required', 'string', 'max:255'],
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')],
            'type' => ['required', 'string', 'max:255', Rule::in($types)],
            'product_id' => ['nullable', 'integer', Rule::exists('products', 'id')],
            'billing' => ['required', 'string', 'max:255', Rule::in($billing)],
            'currency' => ['required', 'string', 'max:255', Rule::in(array_keys(currencies()->toArray()))],
            'price' => ['required', 'numeric'],
            'initial_price' => ['nullable', 'numeric'],
            'server_id' => ['nullable', 'integer', Rule::exists('servers', 'id')],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }

    protected function prepareForValidation()
    {
        $billing = $this->input('billing');

        $this->merge([
            'server_id' => $this->server_id == 'none' ? null : (int)$this->server_id,
            'product_id' => $this->product_id == 'none' ? null : (int)$this->product_id,
        ]);
        if ($billing === 'onetime') {
            $this->merge([
                'expires_at' => null,
            ]);
        }
    }


}
