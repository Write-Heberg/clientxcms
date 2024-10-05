<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Requests\Store\Basket;

use App\Contracts\Store\ProductTypeInterface;
use App\Services\Store\CurrencyService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BasketConfigRequest extends FormRequest
{
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
        $authorizedBilling = collect($this->product->pricingAvailable())->map(function ($price) {
            return $price->recurring;
        })->unique()->toArray();
        /** @var ProductTypeInterface $productType */
        $productType = $this->product->productType();
        $rules = [
            'billing' => ['required', 'string', Rule::in($authorizedBilling)],
            'currency' => ['required', 'string', Rule::in(app(CurrencyService::class)->getCurrenciesKeys())]
        ];
        if ($productType->data($this->product) !== null) {
            $rules = array_merge($rules, $productType->data($this->product)->validate());
        }

        return $rules;
    }
}
