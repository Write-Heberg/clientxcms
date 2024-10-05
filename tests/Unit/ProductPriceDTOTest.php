<?php

use App\Models\Store\Group;
use App\Models\Store\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPriceDTOTest extends TestCase
{
    use RefreshDatabase;
    public function test_product_start_with_empty_prices()
    {
        app(\App\Services\Store\CurrencyService::class)->setCurrency('USD');
        $group = $this->createGroupModel();
        $this->assertEquals(0, $group->startPrice()->price);
    }

    public function test_product_start_price_dto()
    {
        app(\App\Services\Store\CurrencyService::class)->setCurrency('USD');
        $group = $this->createGroupModel();
        for ($i = 1; $i <= 10; $i++) {
            $product = $this->createProductModel();
            $this->createPriceModel($product->id, 'USD',  ['monthly' => $i * 10, 'triennially' => $i * 3 * 10]);
            $group->products()->save($product);
        }
        $this->assertEquals(10, $group->startPrice()->price);
        $this->assertEquals('USD', $group->startPrice()->currency);
        $this->assertEquals('monthly', $group->startPrice()->recurring);
        $this->assertEquals(30, $group->startPrice('triennially')->price);
    }


    protected function createGroupModel(string $status = 'active')
    {
        if (Group::find(1)) {
            return Group::find(1);
        }
        $group = new Group();
        $group->name = 'Test Group';
        $group->slug = 'test-slug';
        $group->description = 'Test Group Description';
        $group->status = $status;
        $group->id = 1;
        $group->save();
        return $group;
    }

    protected function createProductModel(string $status = 'active', int $stock = 1)
    {
        $product = new Product();
        $product->name = 'Test Product';
        $product->status = $status;
        $product->description = 'Test Product Description';
        $product->sort_order = 1;
        $product->group_id = 1;
        $product->stock = $stock;
        $product->type = 'proxmox';
        $product->save();
        return $product;
    }

    protected function createPriceModel(int $product_id, string $currency = 'USD', array $prices = ['monthly' => 1], array $setup = ['monthly' => 1])
    {
        $price = new \App\Models\Store\Pricing();
        $price->related_id = $product_id;
        $price->related_type = 'product';
        $price->currency = $currency;
        $price->onetime = $prices['onetime'] ?? null;
        $price->monthly = $prices['monthly'];
        $price->quarterly = $prices['quarterly'] ?? null;
        $price->semiannually = $prices['semiannually'] ?? null;
        $price->annually = $prices['annually'] ?? null;
        $price->biennially = $prices['biennially'] ?? null;
        $price->triennially = $prices['triennially'] ?? null;
        $price->setup_onetime = $prices['onetime'] ?? null;
        $price->setup_monthly = $prices['monthly'];
        $price->setup_quarterly = $prices['quarterly'] ?? null;
        $price->setup_semiannually = $prices['semiannually'] ?? null;
        $price->setup_annually = $prices['annually'] ?? null;
        $price->setup_biennially = $prices['biennially'] ?? null;
        $price->setup_triennially = $prices['triennially'] ?? null;
        $price->save();
        return $price;
    }
}
