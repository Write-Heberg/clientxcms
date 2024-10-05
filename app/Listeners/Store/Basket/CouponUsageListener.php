<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Listeners\Store\Basket;

use App\Events\Core\CheckoutCompletedEvent;
use App\Models\Store\Coupon;
use App\Models\Store\CouponUsage;

class CouponUsageListener
{
    public function handle(CheckoutCompletedEvent $event): void
    {
        $basket = $event->basket;
        /** @var Coupon $coupon */
        $coupon = $basket->coupon;
        if (!$coupon) {
            return;
        }
        if (!$coupon->isValid($basket)){
            return;
        }
        if ($event->invoice->status !== 'paid') {
            return;
        }
        if ($basket->coupon) {
            $basket->coupon->increment('usages');
        }
        CouponUsage::insert([
            'coupon_id' => $coupon->id,
            'customer_id' => $event->invoice->customer_id,
            'used_at' => now(),
            'amount' => $basket->subtotal()
        ]);
    }
}
