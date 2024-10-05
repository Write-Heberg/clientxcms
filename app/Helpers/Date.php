<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Helpers;

class Date
{
    public static function formatUptime(int $seconds): string
    {
        return date("d:H:i:s", $seconds);
    }
}
