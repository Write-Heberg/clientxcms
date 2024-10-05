<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PricingValidation implements ValidationRule
{
    
    public function message()
    {
        return 'At least one price or setup fee must be set.';
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        foreach ($value as $period => $pricing) {
            if (isset($pricing['price']) || isset($pricing['setup'])) {
                if (!is_null($pricing['price']) || !is_null($pricing['setup'])) {
                    return;
                }
            }
        }
        $fail($this->message());
    }
}
