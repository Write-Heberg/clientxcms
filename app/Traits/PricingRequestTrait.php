<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Traits;

use App\Rules\PricingValidation;

trait PricingRequestTrait
{
    private function pricingRules(): array
    {
        if (str_contains($this->route()->uri, 'api')) {
            return [];
        }
        $rules = [];
        $pricing = $this->get('pricing');
        $rules['pricing'] = [new PricingValidation()];
        if ($pricing) {
            foreach ($pricing as $key => $value) {
                $rules['pricing.' . $key . '.price'] = 'nullable|numeric|min:0';
                $rules['pricing.' . $key . '.setupfee'] = 'nullable|numeric|max:255';
            }
        }
        return $rules;
    }

    private function prepareForPricing(array $pricing)
    {
        $convertedPricing = [];
        foreach ($pricing as $key => $value) {
            $convertedPricing[$key]['price'] = isset($value['price']) ? str_replace(',', '.', $value['price']) : null;
            $convertedPricing[$key]['setup'] = isset($value['setup']) ? str_replace(',', '.', $value['setup']) : null;
        }
        return $convertedPricing;
    }
}
