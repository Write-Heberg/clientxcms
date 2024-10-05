<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Listeners\Store\Basket;

use App\Models\Store\Basket\Basket;
use Illuminate\Auth\Events\Login;

class BasketMerge
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        if ($event->guard !== 'web') {
            return;
        }
        Basket::getBasket()->mergeBasket($event->user);
    }
}
