<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\DTO\Store;

use App\Services\Store\RecurringService;
use App\Services\Store\TaxesService;
use DragonCode\Contracts\Support\Jsonable;

class ProductPriceDTO implements Jsonable
{
    public float $price;
    public bool $free;
    public float $setup;
    public string $currency;
    public string $recurring;
    public float $dbprice;
    public float $dbsetup;

    /**
     * ProductPriceDTO constructor.
     *
     * @param float $price
     * @param float|null $setup
     * @param string $currency
     * @param string $recurring
     */
    public function __construct(float $price, ?float $setup, string $currency, string $recurring)
    {
        $this->dbprice = $price;
        $this->dbsetup = $setup ?? 0;
        $this->price = number_format(TaxesService::getAmount($price, tax_percent()), 2);
        $this->free = $price == 0;
        $this->currency = $currency;
        $this->setup = $setup == null ? 0 : $setup;
        $this->setup = number_format(TaxesService::getAmount($this->setup, tax_percent()), 2);
        $this->recurring = $recurring;
    }

    /**
     * @return bool
     */
    public function isFree(): bool
    {
        return $this->free;
    }

    public function getSymbol(): string
    {
        return currency_symbol($this->currency);
    }

    public function hasSetup(): bool
    {
        return $this->setup > 0;
    }

    public function firstPayment(): float|int
    {
        return $this->price + $this->setup;
    }

    public function recurringPayment(): float
    {
        if ($this->recurring == 'onetime')
            return 0;
        return $this->price;
    }

    public function onetimePayment(): float
    {
        if ($this->recurring != 'onetime')
            return 0;
        return $this->price;
    }

    public function recurring(): array
    {
        return app(RecurringService::class)->get($this->recurring);
    }

    public function toJson(int $options = 0): string
    {
        return json_encode([
            'price' => $this->price,
            'free' => $this->free,
            'setup' => $this->setup,
            'currency' => $this->currency,
            'recurring' => $this->recurring,
            'recurringPayment' => $this->recurringPayment(),
            'onetimePayment' => $this->onetimePayment(),
            'tax' => TaxesService::getTaxAmount($this->dbprice + $this->dbsetup, tax_percent()),
        ]);
    }

    public function getDiscountOnRecurring(ProductPriceDTO $monthlyprice): float
    {
        $monthlyprice = $monthlyprice->price * $this->recurring()["months"];
        if ($monthlyprice == 0) {
            return 0;
        }
        return number_format(($monthlyprice - $this->price - $this->setup) / $monthlyprice * 100, 2, '.', ',');
    }

    public function hasDiscountOnRecurring(ProductPriceDTO $monthlyprice): bool
    {
        $discount = $this->getDiscountOnRecurring($monthlyprice);
        return $discount > 1 && $discount < 100;
    }

    public function pricingMessage(): string
    {
        if ($this->isFree())
            return trans('store.product.freemessage');
        if ($this->hasSetup())
            return trans('store.product.setupmessage', ['first' => $this->firstPayment(), 'recurring' => $this->recurringPayment(), 'currency' => $this->getSymbol(),  'tax' => $this->taxTitle(false), 'unit' => $this->recurring()['unit']]);
        return trans('store.product.nocharge', ['first' => $this->recurringPayment(), 'currency' => $this->getSymbol(), 'unit' => $this->recurring()['unit'], 'tax' => $this->taxTitle(false)]);
    }

    public function taxTitle(bool $disabledHT = true)
    {
        return is_tax_included() ? __('store.ttc') : ($disabledHT ? '' : __('store.ht'));
    }
}
