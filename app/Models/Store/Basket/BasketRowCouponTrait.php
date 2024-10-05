<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Store\Basket;

use App\Services\Store\TaxesService;

trait BasketRowCouponTrait {

    private bool $enableCoupon = false;

    public function enableCoupon(bool $enable = true): void
    {
        $this->enableCoupon = $enable;
    }

    public function taxWithoutCoupon()
    {
        $this->enableCoupon(false);
        return TaxesService::getTaxAmount($this->dbprice(), $this->taxPercent());
    }

    public function subtotalWithoutCoupon()
    {
        $this->enableCoupon(false);
        return $this->recurringPaymentWithoutCoupon() + $this->setupWithoutCoupon() + $this->onetimePaymentWithoutCoupon();
    }

    public function recurringPaymentWithoutCoupon(bool $withQuantity = true, ?string $billing=null)
    {
        $this->enableCoupon(false);

        if ($this->billing == 'onetime') {
            return 0;
        }
        if (!$withQuantity){
            $recurringPayment = $this->product->getPriceByCurrency($this->currency, $billing ?? $this->billing)->price;
        } else {
            $recurringPayment = $this->product->getPriceByCurrency($this->currency, $billing ?? $this->billing)->price * $this->quantity;
        }
        return $this->applyCoupon($recurringPayment, self::PRICE);
    }

    public function onetimePaymentWithoutCoupon(bool $withQuantity = true, ?string $billing=null)
    {
        $this->enableCoupon(false);

        if ($this->billing != 'onetime') {
            return 0;
        }
        if (!$withQuantity){
            $onetimePayment = $this->product->getPriceByCurrency($this->currency, $billing ?? $this->billing)->price;
        } else {
            $onetimePayment = $this->product->getPriceByCurrency($this->currency, $billing ?? $this->billing)->price * $this->quantity;
        }
        return $this->applyCoupon($onetimePayment, self::PRICE);
    }

    public function setupWithoutCoupon(bool $withQuantity = true, ?string $billing=null)
    {
        $this->enableCoupon(false);
        if (!$withQuantity){
            $setup = $this->product->getPriceByCurrency($this->currency, $billing ?? $this->billing)->setup;
        } else {
            $setup = $this->product->getPriceByCurrency($this->currency, $billing ?? $this->billing)->setup * $this->quantity;
        }
        return $this->applyCoupon($setup, self::SETUP_FEES);
    }

    public function totalWithoutCoupon()
    {
        $this->enableCoupon(false);
        dump($this->subtotalWithoutCoupon());
        return $this->subtotalWithoutCoupon() + $this->taxWithoutCoupon();
    }

}
