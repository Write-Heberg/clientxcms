<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Store\Basket;

trait BasketCouponTrait {

    public function subtotalWithoutCoupon()
    {
        return $this->rows->reduce(function ($total, $row) {
            return $total + $row->subtotalWithoutCoupon();
        }, 0);
    }

    public function taxWithoutCoupon()
    {
        return $this->rows->reduce(function ($total, $row) {
            return $total + $row->taxWithoutCoupon();
        }, 0);
    }

    public function totalWithoutCoupon()
    {
        return $this->rows->reduce(function ($total, $row) {
            return $total + $row->totalWithoutCoupon();
        }, 0);
    }

    public function recurringPaymentWithoutCoupon()
    {
        return $this->rows->reduce(function ($total, $row) {
            return $total + $row->recurringPaymentWithoutCoupon();
        }, 0);
    }

    public function setupWithoutCoupon()
    {
        return $this->rows->reduce(function ($total, $row) {
            return $total + $row->setupWithoutCoupon();
        }, 0);
    }

}
