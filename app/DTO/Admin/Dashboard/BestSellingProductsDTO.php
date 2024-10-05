<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\DTO\Admin\Dashboard;

use App\DTO\Admin\Dashboard\Earn\GatewaysCanvasDTO;
use App\Models\Core\Invoice;
use App\Models\Core\InvoiceItem;
use App\Models\Store\Product;

class BestSellingProductsDTO
{

    public ?Product $first = null;
    public ?Product $second = null;
    public ?Product $third = null;
    public int $firstCount = 0;
    public int $secondCount = 0;
    public int $thirdCount = 0;

    public function __construct(array $data)
    {
        if (count($data) > 0) {
            $this->first = Product::find($data[0]['related_id']);
            $this->firstCount = $data[0]['count'];
        }
        if (count($data) > 1) {
            $this->second = Product::find($data[1]['related_id']);
            $this->secondCount = $data[1]['count'];
        }
        if (count($data) > 2) {
            $this->third = Product::find($data[2]['related_id']);
            $this->thirdCount = $data[2]['count'];
        }
        if ($this->first == null) {
            $this->first = $this->defaultProduct();
        }
        if ($this->second == null) {
            $this->second = $this->defaultProduct();
        }
        if ($this->third == null) {
            $this->third = $this->defaultProduct();
        }
    }

    public static function getBestProducts()
    {
        $products = InvoiceItem::whereType('service')->groupBy('related_id')->selectRaw('related_id, count(*) as count')->orderBy('count', 'desc')->limit(3)->get();
        return new self($products->toArray());
    }

    public static function getBestProductsLastWeek()
    {
        $products = InvoiceItem::whereType('service')->where('created_at', '>=', now()->subWeek())->groupBy('related_id')->selectRaw('related_id, count(*) as count')->orderBy('count', 'desc')->limit(3)->get();
        return new self($products->toArray());
    }

    public static function getBestProductsLastMonth()
    {
        $products = InvoiceItem::whereType('service')->where('created_at', '>=', now()->subMonth())->groupBy('related_id')->selectRaw('related_id, count(*) as count')->orderBy('count', 'desc')->limit(3)->get();
        return new self($products->toArray());
    }

    public static function getGateways()
    {
        return new GatewaysCanvasDTO(Invoice::groupBy('paymethod')->selectRaw('paymethod, count(id) as count')->orderBy('count', 'desc')->get());
    }

    public function getLastWeekLabel()
    {
        return __('admin.dashboard.widgets.best_selling.last_week', ['date' => now()->subWeek()->format('d/m')]);
    }

    public function getLastMonthLabel()
    {
        return __('admin.dashboard.widgets.best_selling.last_month', ['month' => now()->subMonth()->isoFormat('MMMM')]);
    }

    public static function getDetailedProducts()
    {
        return InvoiceItem::whereType('service')->groupBy('related_id')->selectRaw('related_id, count(id) as count')->selectRaw('sum(unit_price) as price')->orderBy('price', 'desc')->get();
    }

    private function defaultProduct()
    {
        return new Product([
            'name' => 'No product',
        ]);
    }


}
