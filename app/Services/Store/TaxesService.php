<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Services\Store;

class TaxesService
{
    const MODE_TAX_INCLUDED = 'tax_included';
    const MODE_TAX_EXCLUDED = 'tax_excluded';
    const VAT_PERCENT_METADATA_KEY = 'vat_percent';
    const VAT_DISABLED_METADATA_KEY = 'vat_disabled';

    /**
     * @param string|null $mode
     * @param float $price
     * @param float $taxPercent
     * @return float
     */
    public static function getTaxAmount(float $price, float $taxPercent, ?string $mode = null): float
    {
        if ($mode == null)
            $mode = config('store_mode_tax', self::MODE_TAX_EXCLUDED);
        if ($price != 0)
        if ($mode === self::MODE_TAX_EXCLUDED) {
            return round(($price * ($taxPercent / 100)), 2);
        }
        return round($price * ($taxPercent / 100), 2);
    }

    public static function getAmount(float $price, float $taxPercent, ?string $mode = null): float
    {
        if ($mode == null)
            $mode = setting('store_mode_tax', self::MODE_TAX_EXCLUDED);
        if ($mode === self::MODE_TAX_EXCLUDED) {
            return round($price - self::getTaxAmount($price, $taxPercent, $mode), 2);
        }
        return round($price, 2);
    }

    public static function getVatPercent(?string $iso=null)
    {
        $enabled = setting('store_vat_enabled', true);
        if (!$enabled) {
            return 0;
        }
        if ($iso == null) {

            if (env('STORE_UNIQUE_PERCENTAGE') != null) {
                return env('STORE_UNIQUE_PERCENTAGE');
            }
            if (auth()->check()) {
                $iso = auth()->user()->country;
                if (auth()->user()->getMetadata(self::VAT_PERCENT_METADATA_KEY)) {
                    return floatval(auth()->user()->getMetadata(self::VAT_PERCENT_METADATA_KEY));
                }
                if (auth()->user()->getMetadata(self::VAT_DISABLED_METADATA_KEY)) {
                    return 0;
                }
            } else {
                $iso = env('STORE_DEFAULT_COUNTRY', 'FR');
            }
        }
        return [
            'AT' => 20,
            'BE' => 21,
            'BG' => 20,
            'HR' => 25,
            'CY' => 19,
            'CZ' => 21,
            'DK' => 25,
            'EE' => 20,
            'DE' => 19,
            'GR' => 24,
            'FI' => 24,
            'FR' => 20,
            'HU' => 27,
            'IE' => 23,
            'IT' => 22,
            'LV' => 21,
            'LT' => 21,
            'LU' => 17,
            'MT' => 18,
            'NL' => 21,
            'PL' => 23,
            'PT' => 23,
            'RO' => 19,
            'SK' => 20,
            'SI' => 22,
            'ES' => 21,
            'SE' => 25,
            'GB' => 20,
        ][strtoupper($iso)] ?? 20;
    }
}
