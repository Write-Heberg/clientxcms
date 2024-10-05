<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Requests\Store;

use App\Models\Store\Pricing;
use App\Services\Store\CurrencyService;
use Illuminate\Foundation\Http\FormRequest;

class StorePricingRequest extends FormRequest
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
        $currencies = app(CurrencyService::class)->getCurrencies()->keys()->implode(',');
        return [
            'related_id' => 'required|integer',
            'related_type' => 'required|string|in:' . implode(',', Pricing::ALLOWED_TYPES),
            'currency' => 'required|string|in:'. $currencies,
            'onetime' => 'nullable|numeric|min:0|max:999999.99',
            'monthly' => 'nullable|numeric|min:0|max:999999.99',
            'quarterly' => 'nullable|numeric|min:0|max:999999.99',
            'semiannually' => 'nullable|numeric|min:0|max:999999.99',
            'annually' => 'nullable|numeric|min:0|max:999999.99',
            'biennially' => 'nullable|numeric|min:0|max:999999.99',
            'triennially' => 'nullable|numeric|min:0|max:999999.99',
            'setup_onetime' => 'nullable|numeric|min:0|max:999999.99',
            'setup_monthly' => 'nullable|numeric|min:0|max:999999.99',
            'setup_quarterly' => 'nullable|numeric|min:0|max:999999.99',
            'setup_semiannually' => 'nullable|numeric|min:0|max:999999.99',
            'setup_annually' => 'nullable|numeric|min:0|max:999999.99',
            'setup_biennially' => 'nullable|numeric|min:0|max:999999.99',
            'setup_triennially' => 'nullable|numeric|min:0|max:999999.99',
        ];
    }
}
