<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Hostname implements Rule
{

    public function passes($attribute, $value)
    {
        if (strlen($value) > 253) {
            return false;
        }

        $labels = explode('.', $value);

        foreach ($labels as $label) {
            if (!preg_match('/^[a-zA-Z0-9-]+$/', $label)) {
                return false;
            }
            if (strlen($label) > 63 || strlen($label) == 0) {
                return false;
            }
            if (str_starts_with($label, '-') || strrpos($label, '-') === strlen($label) - 1) {
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return 'Invalid Hostname.';
    }
}
