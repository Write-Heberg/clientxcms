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

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $states = array_keys(Service::getStatuses());
        $types = app('extension')->getProductTypes()->keys()->merge(['none'])->toArray();
        $billing = app(RecurringService::class)->getRecurringTypes();
        return [
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255', Rule::in($types)],
            'status' => ['required', 'string', 'max:255', Rule::in($states)],
            'price' => ['required', 'numeric'],
            'billing' => ['required', 'string', 'max:255', Rule::in($billing)],
            'initial_price' => ['numeric'],
            'server_id' => ['nullable', 'integer', Rule::exists('servers', 'id')],
            'expires_at' => ['nullable', 'date'],
            'product_id' => ['nullable', 'integer', Rule::exists('products', 'id')],
            'suspended_at' => ['nullable', 'date'],
            'cancelled_at' => ['nullable', 'date'],
            'cancelled_reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'delivery_errors' => ['nullable', 'string', 'max:1000'],
            'delivery_attempts' => ['nullable', 'integer'],
            'renewals' => ['nullable', 'integer'],
            'currency' => ['nullable', 'string', Rule::in(array_keys(currencies()->toArray()))],
            'trial_ends_at' => ['nullable', 'date'],
            'max_renewals' => ['nullable', 'integer'],
            'data' => ['nullable', 'array'],
        ];

    }


    protected function prepareForValidation()
    {
        $billing = $this->input('billing');
        $this->merge([
            'product_id' => $this->product_id == 'none' ? null : (int)$this->product_id,
            'server_id' => $this->server_id == 'none' ? null : (int)$this->server_id,
        ]);
        if ($billing === 'onetime') {
            $this->merge([
                'expires_at' => null,
            ]);
        }
    }
}
