<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class JsonToObject implements CastsAttributes
{
    /**
    * Cast the given value.
    *
    * @param  \Illuminate\Database\Eloquent\Model  $model
    * @param  string  $key
    * @param  mixed  $value
    * @param  array  $attributes
    * @return mixed
    */
    public function get($model, $key, $value, $attributes)
    {
        if (is_null($value)) {
            return null;
        }
        return json_decode($value);
    }

    /**
    * Prepare the given value for storage.
    *
    * @param  \Illuminate\Database\Eloquent\Model  $model
    * @param  string  $key
    * @param  mixed  $value
    * @param  array  $attributes
    * @return mixed
    */
    public function set($model, $key, $value, $attributes)
    {
        if (is_null($value)) {
            return null;
        }
        return json_encode($value);
    }
}
