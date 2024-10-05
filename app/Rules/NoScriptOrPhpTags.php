<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NoScriptOrPhpTags implements Rule
{
    public function passes($attribute, $value)
    {
        $content = file_get_contents($value->getRealPath());
        if (str_contains($content, '<script>') || str_contains($content, '<?php')) {
            return false;
        }
        return true;
    }

    public function message()
    {
        return 'The :attribute contains forbidden script or PHP tags.';
    }
}
