<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Helpers;

class Countries
{
    public static array $countries = [];

    public static function all() {
        if (empty(self::$countries)){
            self::$countries = json_decode(file_get_contents(resource_path('countries.json'), true));
        }
        return self::$countries;
    }

    public static function names():array {
        $names = [];
        foreach (self::all() as $country){
            $names[$country->alpha_2_code] = $country->en_short_name;
        }
        return $names;
    }

    public static function rule()
    {
        $names = join(',',array_keys(self::names()));
        return "phone:{$names}";
    }
}
