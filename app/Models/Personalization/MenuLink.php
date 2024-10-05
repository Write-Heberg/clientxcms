<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Personalization;

use Illuminate\Database\Eloquent\Model;

class MenuLink extends Model
{
    protected $table = "theme_menu_links";

    protected $fillable = [
        'name',
        'position',
        'type',
        'items',
    ];

    protected $casts = [
        'items' => 'array',
    ];

    public static function newFrontMenu()
    {
        return [
                'name' => 'Front',
                'position' => '1',
                'type' => 'front',
                'items' => [
                    [
                        'name' => 'Home',
                        'url' => '/',
                        'icon' => 'bi bi-house-door',
                    ],
                    [
                        'name' => 'Store',
                        'url' => '/store',
                        'icon' => 'bi bi-shop',
                    ],
                    [
                        'name' => 'Helpdesk',
                        'url' => '/client/support',
                        'icon' => 'bi bi-chat-left-text',
                    ],
                ],
            ];
        }

        public static function newBottonMenu()
        {
            return [
                'name' => 'Footer',
                'position' => '1',
                'type' => 'bottom',
                'items' => [
                    [
                        'name' => 'Condition of use',
                        'url' => '/condition-of-use',
                    ],
                    [
                        'name' => 'Privacy policy',
                        'url' => '/privacy-policy',
                    ],
                    [
                        'name' => 'Status of services',
                        'url' => 'https://status.clientxcms.com',
                    ],
                ],
            ];
        }
}
