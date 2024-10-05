<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Navigation;

class MainNavigationMenu
{
    public static function getItems(): array
    {
        return [
            [
                'name' => __('global.store'),
                'route' => 'front.store.index',
            ],

            [
                'name' => __('global.basket'),
                'route' => 'front.store.basket',
            ]
        ];
    }
}
