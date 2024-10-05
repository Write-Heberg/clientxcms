<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin\Store;

use App\Core\NoneProductType;
use App\Events\Resources\ResourceCloneEvent;
use App\Http\Controllers\Admin\AbstractCrudController;
use App\Http\Requests\Store\ConfigProductRequest;
use App\Http\Requests\Store\StoreProductRequest;
use App\Http\Requests\Store\UpdateGroupRequest;
use App\Http\Requests\Store\UpdateProductRequest;
use App\Models\Store\Group;
use App\Models\Store\Pricing;
use App\Models\Store\Product;
use App\Services\Store\PricingService;
use App\Services\Store\RecurringService;

class ProductController extends AbstractCrudController
{
    protected string $viewPath = 'admin.store.products';
    protected string $routePath = 'admin.products';
    protected string $translatePrefix = 'admin.products';
    protected string $model = Product::class;
    protected int $perPage = 25;
    protected string $searchField = 'name';
    protected ?string $managedPermission = 'admin.manage_products';
    protected string $filterField = 'group_id';

    public function getCreateParams()
    {
        $data = parent::getCreateParams();
        $data['types'] = $this->types();
        $data['groups'] = Group::all()->pluck('name', 'id')->toArray();
        $data['pricing'] = new Pricing();
        $data['recurrings'] = (new RecurringService())->getRecurrings();
        return $data;
    }

    protected function getSearchFields()
    {
        return [
            'name' => __('global.name'),
            'group.name' => __('admin.products.group'),
        ];
    }

    public function show(Product $product)
    {
        $this->checkPermission('show');
        $params['item'] = $product;
        $params['types'] = $this->types();
        $params['groups'] = Group::all()->pluck('name', 'id')->toArray();
        $params['pricing'] = Pricing::where('related_id', $product->id)->where('related_type', 'product')->first();
        $params['recurrings'] = (new RecurringService())->getRecurrings();
        $params['configForm'] = $product->productType()->config() ? $product->productType()->config()->render($product) : null;
        if ($params['pricing'] == null){
            $params['pricing'] = new Pricing();
        }
        return $this->showView($params);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->checkPermission('update');
        $product->update($request->only('name', 'description', 'status', 'group_id', 'stock', 'type', 'pinned', 'sort_order'));
        $pricing = Pricing::where('related_id', $product->id)->where('related_type', 'product')->first();
        if ($pricing == null){
            $pricing = new Pricing();
            $pricing->related_id = $product->id;
            $pricing->related_type = 'product';
        }
        $pricing->updateFromArray($request->only('pricing'));
        PricingService::forgot();
        return $this->updateRedirect($product);
    }

    public function store(StoreProductRequest $request)
    {
        $this->checkPermission('create');
        $product = Product::create($request->only('name', 'description', 'status', 'group_id', 'stock', 'type', 'pinned', 'sort_order'));
        Pricing::createFromArray($request->only('pricing'), $product->id);
        PricingService::forgot();
        if ($request->get('type') == 'none'){
            return $this->storeRedirect($product);
        }
        return redirect()->to(route($this->routePath . '.show', ['product' => $product]) . '#config')->with('success', __($this->flashs['created']));
    }

    public function config(ConfigProductRequest $request, Product $product) {
        $this->checkPermission('update');
        $config = $product->productType()->config();
        if ($config == null) {
            return redirect()->route($this->routePath . '.show', ['product' => $product->id])->with('error', __('admin.products.config.notfound'));
        }
        if ($config->getConfig($product->id) == null) {
            $config->storeConfig($product, $request->validated());
        } else {
            $config->updateConfig($product, $request->validated());
        }
        return redirect()->route($this->routePath . '.show', ['product' => $product->id])->with('success', __('admin.products.config.success'));
    }

    public function clone(Product $product)
    {
        $this->checkPermission('create');
        $newProduct = $product->replicate();
        $newProduct->name = $product->name . ' - Clone';
        $newProduct->save();
        $pricing = Pricing::where('related_id', $product->id)->where('related_type', 'product')->first();
        if ($pricing != null){
            $newPricing = $pricing->replicate();
            $newPricing->related_id = $newProduct->id;
            $newPricing->save();
        }
        if ($product->productType()->config() != null){
            $product->productType()->config()->cloneConfig($product, $newProduct);
        }
        event(new ResourceCloneEvent($product));
        return $this->storeRedirect($newProduct);
    }

    private function types()
    {
        return app('extension')->getProductTypes()->merge(['none' => new NoneProductType()])->mapWithKeys(function ($k, $v) {
            return [$v => $k->title()];
        });
    }

    protected function getIndexFilters()
    {
        return Group::all()->pluck('name', 'id')->toArray();
    }

    public function destroy(Product $product)
    {
        Pricing::where('related_id', $product->id)->where('related_type', 'product')->delete();
        $product->delete();
        return $this->deleteRedirect($product);
    }
}
