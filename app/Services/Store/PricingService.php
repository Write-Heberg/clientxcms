<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Services\Store;

class PricingService
{

    public static function fetch()
    {
        if (!is_installed()) {
            return [];
        }
        return \Cache::remember('pricing', 60 * 60 * 24, function () {
            return \App\Models\Store\Pricing::get()->toArray();
        });
    }

    public static function forgot()
    {
        \Cache::forget('pricing');
    }

    public static function forProduct(int $product_id)
    {
        return collect(self::fetch())->where('related_id', $product_id)->where('related_type', 'product');
    }

    public static function forProductCurrency(int $product_id, string $currency)
    {
        return collect(self::forProduct($product_id))->where('currency', $currency)->first();
    }


}
