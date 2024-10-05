<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Front\Store\Basket;

use App\DTO\Store\ProductDataDTO;
use App\Exceptions\WrongPaymentException;
use App\Helpers\Countries;
use App\Http\Requests\ProcessCheckoutRequest;
use App\Http\Requests\Store\Basket\BasketConfigRequest;
use App\Models\Core\Gateway;
use App\Models\Store\Basket\Basket;
use App\Models\Store\Basket\BasketRow;
use App\Models\Store\Product;
use App\Services\Account\AccountEditService;
use App\Services\Core\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;


class BasketController extends \App\Http\Controllers\Controller
{
    public function addProduct(Product $product)
    {
        if ($product->isNotValid(true)) {
            return back()->with('error', __('store.basket.not_valid'));
        }
        if ($product->hasPricesForCurrency() !== true) {
            return back()->with('error', __('store.basket.no_prices'));
        }
        if (!$product->canAddToBasket()){
            return back()->with('error', __('store.basket.already_ordered', ['product' => $product->name]));
        }
        $basket = Basket::firstOrCreate([
            'user_id' => auth()->id(),
            'uuid' => Basket::getUUID(),
            'completed_at' => null,
        ]);
        return redirect()->route('front.store.basket.config', ['product' => $product]);
    }

    public function show()
    {
        return view('front.store.basket.show', [
            'basket' => Basket::getBasket()
        ]);
    }

    public function showConfigProduct(Product $product)
    {
        if ($product->isNotValid(true)) {
            return back()->with('error', __('store.basket.not_valid'));
        }
        $row = BasketRow::findByProductOnSession($product, false);
        $billing = $row->billing;
        $available = $product->pricingAvailable(currency());
        if ($product->getPriceByCurrency(currency(), $billing)->price == 0 && count($available) > 0) {
            $billing = $available[0]->recurring;
        }

        if (!$product->canAddToBasket()){
            return back()->with('error', __('store.basket.already_ordered', ['product' => $product->name]));
        }
        $context = ['product' => $product, 'options' => [], 'billing' => $billing, 'row' => $row];
        if ($product->productType()->data($product) !== null) {
            $context['data_html'] = $product->productType()->data($product)->render(new ProductDataDTO($product, $row->data ?? [], $context['options'] ?? [], []));
        } else {
            $context['data_html'] = '';
        }
        return view('front.store.basket.config', $context);
    }

    public function configProduct(Product $product, BasketConfigRequest $request)
    {
        $row = BasketRow::findByProductOnSession($product);
        $row->billing = $request->billing;
        $row->currency = $request->currency;
        if ($product->productType()->data($product) != null){
            $data = $product->productType()->data($product)->parameters(new ProductDataDTO($product, $row->data ?? [], $request->validated())) + $request->validated();
        } else {
            $data = $request->validated();
        }
        if (array_key_exists('error', $data)){
            return back()->with('error', $data['error']);
        }
        if ($product->isNotValid(true)) {
            return back()->with('error', __('store.basket.not_valid'));
        }
        if (!$product->canAddToBasket()){
            return back()->with('error', __('store.basket.already_ordered', ['product' => $product->name]));
        }
        $row->data = $data;
        $row->save();
        return redirect()->route('front.store.basket.show')->with('success', __('store.basket.added'));
    }

    public function removeRow(Product $product)
    {
        $row = BasketRow::findByProductOnSession($product);
        $row->delete();
        return redirect()->route('front.store.basket.show')->with('success', __('store.basket.removed'));
    }

    public function changeQuantity(Product $product)
    {
        $row = BasketRow::findByProductOnSession($product);
        $row->quantity = request()->has('plus') ? $row->quantity + 1 : $row->quantity - 1;
        if ($row->quantity < 1 || $row->quantity > $product->stock || $product->isNotValid(true) || $row->quantity > 100) {
            $row->delete();
            return redirect()->route('front.store.basket.show')->with('success', __('store.basket.removed'));
        }
        if ($product->hasMetadata('disabled_many_services') && $row->quantity > 1){
            return redirect()->route('front.store.basket.show')->with('error',  __('store.basket.already_ordered', ['product' => $product->name]));
        }
        $row->save();
        return redirect()->route('front.store.basket.show')->with('success', __('store.basket.quantity_changed'));
    }

    public function showCheckout()
    {
        $basket = Basket::getBasket();
        $this->checkPrerequisites(true, $basket, 'front.store.basket.show');
        return view('front.store.basket.checkout', [
            'basket' => $basket,
            'countries' => Countries::names(),
            'gateways' => \App\Models\Core\Gateway::getAvailable()->get(),
        ]);
    }

    public function processCheckout(ProcessCheckoutRequest $request)
    {
        $basket = Basket::getBasket();
        $prerequisite = $this->checkPrerequisites(false, $basket, 'front.store.basket.checkout');
        if ($prerequisite !== true) {
            return $prerequisite;
        }
        /** @var Gateway|null $gateway */
        $gateway = \App\Models\Core\Gateway::getAvailable()->where('uuid', $request->gateway)->first();
        if ($gateway === null) {
            return redirect()->route('front.store.basket.checkout')->with('error', __('store.checkout.gateway_not_found'));
        }
        AccountEditService::saveCurrentCustomer($request->validated());

        $invoice = InvoiceService::createInvoiceFromBasket($basket, $gateway);
        try {
            return $invoice->pay($gateway, $request);
        } catch (WrongPaymentException $e) {
            logger()->error($e->getMessage());
            $message = __('store.checkout.wrong_payment');
            if (auth('admin')->check()) {
                $message .= ' Debug admin : ' . $e->getMessage();
            }
            return redirect()->route('front.store.basket.checkout')->with('error', $message);
        }
    }

    public function coupon(Request $request)
    {
        $this->validate($request, [
            'coupon' => 'required|string',
        ]);
        $basket = Basket::getBasket();
        $apply = $basket->applyCoupon($request->coupon);
        if ($apply === true) {
            return redirect()->route('front.store.basket.show')->with('success', __('coupon.coupon_applied'));
        }
        return redirect()->route('front.store.basket.show');
    }

    public function removeCoupon()
    {
        $basket = Basket::getBasket();
        $basket->update(['coupon_id' => null]);
        return redirect()->route('front.store.basket.show')->with('success', __('coupon.coupon_removed'));
    }

    private function checkPrerequisites(bool $flash, Basket $basket, string $route)
    {
        if (!$basket->checkCurrency()) {
            if ($flash){
                session()->flash('warning', __('store.checkout.invalidcurrency'));
            }
            return redirect()->route($route)->with('warning', __('store.checkout.invalidcurrency'));
        }
        if (!$basket->checkValid()) {
            if ($flash){
                session()->flash('warning', __('store.checkout.invalidproduct'));
            }
            return redirect()->route($route)->with('warning', __('store.checkout.invalidproduct'));
        }
        if (auth('web')->guest()) {
            if ($flash){
                session()->flash('warning', __('store.checkout.mustbelogged'));
            }
            return redirect()->route($route)->with('warning', __('store.checkout.mustbelogged'));
        }
        if (auth('web')->user() && auth('web')->user()->hasVerifiedEmail() !== true && setting('checkout.customermustbeconfirmed', false) === true) {
            if ($flash){
                session()->flash('warning', __('store.checkout.mustbeconfirmed'));
            }
            return redirect()->route($route)->with('warning', __('store.checkout.mustbeconfirmed'));
        }
        if ($basket->rows->count() == 0) {
            if ($flash){
                session()->flash('warning', __('store.checkout.empty'));
            }
            return redirect()->route($route)->with('warning', __('store.basket.empty'));
        }
        return true;
    }

}
