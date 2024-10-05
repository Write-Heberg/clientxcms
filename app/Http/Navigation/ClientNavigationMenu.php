<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Navigation;

class ClientNavigationMenu
{
    public static function getItems(): array
    {
        return [
            [
                'name' => __('global.clientarea'),
                'route' => 'front.client.index',
                'icon' => 'bi bi-speedometer'
            ],
            [
                'name' => __('client.services.index'),
                'route' => 'front.services.index',
                'icon' => 'bi bi-box2',
            ],
            [
                'name' => __('client.invoices.index'),
                'route' => 'front.invoices.index',
                'icon' => 'bi bi-receipt'
            ],
            [
                'name' => __('client.emails.index'),
                'route' => 'front.emails.index',
                'icon' => 'bi bi-envelope'
            ],

            [
                'name' => __('client.profile.index'),
                'route' => 'front.profile.index',
                'icon' => 'bi bi-person-lines-fill'
            ],
            [
                'name' => __('client.support.index'),
                'route' => 'front.support.index',
                'icon' => 'bi bi-chat-left-text'
            ]
        ];
    }
}
