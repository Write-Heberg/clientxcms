<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Personalization;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialNetwork extends Model
{
    protected $table = "theme_socialnetworks";
    use HasFactory;

    protected $fillable = [
        'icon',
        'name',
        'url',
    ];

    public static function getSvgFromResource(string $name): string
    {
        return file_get_contents(resource_path("svg/socials/{$name}.svg"));
    }
}
