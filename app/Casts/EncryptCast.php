<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class EncryptCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        return ! is_null($value) ? decrypt($value) : null;
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return [$key => ! is_null($value) ? encrypt($value) : null];
    }
}
