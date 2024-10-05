<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Services\Core;

use App\DTO\Admin\Invoice\AddProductToInvoiceDTO;
use App\DTO\Store\ProductPriceDTO;
use App\Events\Core\Invoice\InvoiceCreated;
use App\Models\Core\Gateway;
use App\Models\Core\Invoice;
use App\Models\Core\InvoiceItem;
use App\Models\Provisioning\Service;
use App\Models\Provisioning\ServiceRenewals;
use App\Models\Store\Basket\Basket;
use App\Models\Store\Basket\BasketRow;
use App\Models\Store\Coupon;
use App\Models\Store\Product;
use App\Services\Store\RecurringService;
use App\Services\Store\TaxesService;
use Carbon\Carbon;

class InvoiceService
{

    const PRO_FORMA = 'proforma';
    const INVOICE = 'invoice';

    public static function createInvoiceFromBasket(Basket $basket, Gateway $gateway):Invoice
    {
        $currency = $basket->items->first()->currency;
        // Si une facture est déjà liée au panier, on la met à jour
        if ($basket->getMetadata('invoice') != null) {
            $invoice = Invoice::find($basket->getMetadata('invoice'));
            if ($invoice != null) {
                $invoice->update([
                    'customer_id' => $basket->user_id,
                    'due_date' => now()->addDays(7),
                    'total' => $basket->total(),
                    'subtotal' => $basket->subtotal(),
                    'tax' => $basket->tax(),
                    'setupfees' => $basket->setup(),
                    'currency' => $currency,
                    'status' => 'pending',
                    'notes' => "Created from basket #{$basket->id}",
                    'paymethod' => $gateway->uuid,
                ]);
                $invoice->items()->delete();
                $basket->items->each(function (BasketRow $item) use ($invoice) {
                    $invoice->items()->create([
                        'invoice_id' => $invoice->id,
                        'name' => $item->product->name,
                        'description' => 'Created from basket item',
                        'quantity' => $item->quantity,
                        'unit_price' => $item->recurringPaymentWithoutCoupon(false) + $item->onetimePaymentWithoutCoupon(false),
                        'unit_setupfees' => $item->setupWithoutCoupon(false),
                        'total' => $item->total(),
                        'tax' => $item->tax(),
                        'subtotal' => $item->subtotal(),
                        'setupfee' => $item->setupWithoutCoupon(),
                        'type' => $item->product->productType()->type(),
                        'related_id' => $item->product->id,
                        'data' => $item->data,
                        'discount' => $item->getDiscountArray(),
                        'unit_original_price' => $item->product->getPriceByCurrency($item->currency, $item->billing)->dbprice,
                        'unit_original_setupfees' => $item->product->getPriceByCurrency($item->currency, $item->billing)->dbsetup,
                    ]);
                });
                return $invoice;
            }
        }
        $days = setting('remove_pending_invoice', 0) != 0 ? setting('remove_pending_invoice') : 7;
        $invoice = Invoice::create([
            'customer_id' => $basket->user_id,
            'due_date' => now()->addDays($days),
            'total' => $basket->total(),
            'subtotal' => $basket->subtotal(),
            'tax' => $basket->tax(),
            'setupfees' => $basket->setup(),
            'currency' => $currency,
            'status' => 'pending',
            'notes' => "Created from basket #{$basket->id}",
            'paymethod' => $gateway->uuid,
            'invoice_number' => Invoice::generateInvoiceNumber(),
        ]);
        $basket->items->each(function (BasketRow $item) use ($invoice) {
            $invoice->items()->create([
                'invoice_id' => $invoice->id,
                'name' => $item->product->name,
                'description' => 'Created from basket item',
                'quantity' => $item->quantity,
                'unit_price' => $item->recurringPaymentWithoutCoupon(false) + $item->onetimePaymentWithoutCoupon(false),
                'unit_setupfees' => $item->setupWithoutCoupon(false),
                'total' => $item->total(),
                'tax' => $item->tax(),
                'subtotal' => $item->subtotal(),
                'setupfee' => $item->setupWithoutCoupon(),
                'discount' => $item->getDiscountArray(),
                'type' => $item->product->productType()->type(),
                'related_id' => $item->product->id,
                'unit_original_price' => $item->product->getPriceByCurrency($item->currency, $item->billing)->dbprice,
                'unit_original_setupfees' => $item->product->getPriceByCurrency($item->currency, $item->billing)->dbsetup,
                'data' => $item->data,
            ]);
        });
        $basket->attachMetadata('invoice', $invoice->id);
        $invoice->attachMetadata('basket', $basket->id);
        event(new InvoiceCreated($invoice));
        return $invoice;
    }

    public static function createServicesFromInvoiceItem(Invoice $invoice, InvoiceItem $item): array
    {
        $product = $item->relatedType();
        $expiresAt = app(RecurringService::class)->addFromNow($item->billing());
        if ($item->billing() == 'onetime'){
            $next = null;
        } else {
            $next = app(RecurringService::class)->addFromNow($item->billing())->subDays(setting('core.services.days_before_creation_renewal_invoice'));
        }
        if ($product == null){
            throw new \Exception('Product not found');
        }
        if ($product->productType()->server() != null){
            $server = $product->productType()->server()->findServer($product);
            if ($server != null){
                $server = $server->id;
            } else {
                $server = null;
            }
        } else {
            $server = null;
        }
        $servicesIds = [];
        $services = [];
        for ($i = 0; $i < $item->quantity; $i++) {
            $service = Service::create([
                'customer_id' => $invoice->customer_id,
                'type' => $product->productType()->uuid(),
                'status' => 'pending',
                'name' => $item->name,
                'price' => $item->unit_price,
                'billing' => $item->billing(),
                'initial_price' => $item->unit_price,
                'product_id' => $item->related_id,
                'server_id' => $server,
                'invoice_id' => NULL,
                'expires_at' => $expiresAt,
                'data' => $item->data,
                'currency' => $invoice->currency,
                'max_renewals' => ((int)$product->getMetadata('max_renewals')) ?? null,
            ]);
            foreach (['week', 'month'] as $period){
                if ($product->getMetadata('max_renewals_in_current_'.$period)){
                    $service->attachMetadata('max_renewals_in_current_'.$period, $product->getMetadata('max_renewals_in_current_'.$period));
                }
            }
            if ($item->getDiscount(false)){
                $service->attachMetadata('discount', json_encode($item->discount));
                $service->attachMetadata('default_price', $item->unit_original_price);

                $service->update([
                    'initial_price' => $service->generateDiscountedPrice($service->billing),
                    'price' => $service->generateDiscountedPrice($service->billing),
                ]);
            }
            $servicesIds[] = $service->id;
            $services[] = $service;
            ServiceRenewals::insert([
                'service_id' => $service->id,
                'invoice_id' => $invoice->id,
                'start_date' => Carbon::now(),
                'end_date' => $expiresAt,
                'first_period' => true,
                'next_billing_on' => $next,
                'created_at' => Carbon::now(),
                'period' => 1,
            ]);
        }

        $item->attachMetadata('services', implode(',', $servicesIds));

        return $services;
    }

    public static function createInvoiceFromService(Service $service)
    {
        $price = $service->renewPrice($service->currency, $service->billing, true);
        $discount = $price - $service->generateDiscountedPrice($service->billing);
        $tax = TaxesService::getTaxAmount($price - $discount, tax_percent());
        $total = TaxesService::getAmount($price - $discount, tax_percent()) + $tax;
        $currency = $service->currency;
        $months = $service->recurring()['months'];
        $days = setting('remove_pending_invoice', 0) != 0 ? setting('remove_pending_invoice') : 7;
        $invoice = Invoice::create([
            'customer_id' => $service->customer_id,
            'due_date' => now()->addDays($days),
            'total' => $total,
            'subtotal' => $price - $discount,
            'tax' => $tax,
            'setupfees' => 0,
            'currency' => $currency,
            'status' => 'pending',
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'notes' => "Add extra {$months} month to service #{$service->id} ({$service->name})",
        ]);
        $current = $service->expires_at->format('d/m/y');
        $expiresAt = app(RecurringService::class)->addFrom($service->expires_at, $service->billing);
        $nextBilling = app(RecurringService::class)->addFrom(app(RecurringService::class)->addFrom($service->expires_at, $service->billing), $service->billing)->subDays(setting('core.services.days_before_creation_renewal_invoice'));
        $name = "{$service->name} ({$current} - {$expiresAt->format('d/m/y')})";
        $invoice->items()->create([
            'invoice_id' => $invoice->id,
            'name' => $name,
            'description' => "Add extra {$months} month to service #{$service->id} ({$service->name})",
            'quantity' => 1,
            'unit_price' => $price,
            'unit_setupfees' => 0,
            'total' => $total,
            'tax' => $tax,
            'subtotal' => $price - $discount,
            'setupfee' => 0,
            'type' => 'renewal',
            'related_id' => $service->id,
            'data' => ['months' => $months],
            'unit_original_price' => $price - $discount,
            'unit_original_setupfees' => 0,
            'discount' => $service->getDiscountRenewal(),
        ]);

        ServiceRenewals::insert([
            'service_id' => $service->id,
            'invoice_id' => $invoice->id,
            'start_date' => $current,
            'end_date' => $expiresAt,
            'period' => $service->getAttribute('renewals') + 1,
            'next_billing_on' => $nextBilling,
            'created_at' => Carbon::now(),
        ]);
        event(new InvoiceCreated($invoice));
        return $invoice;
    }

    public static function appendServiceOnExistingInvoice(Service $service, Invoice $invoice)
    {
        $price = $service->renewPrice($service->currency, $service->billing);
        $tax = TaxesService::getTaxAmount($price, tax_percent());
        $total = TaxesService::getAmount($price, tax_percent()) + $tax;
        $months = $service->recurring()['months'];
        $current = $service->expires_at->format('d/m/y');
        $expiresAt = app(RecurringService::class)->addFrom($service->expires_at, $service->billing);
        $nextBilling = app(RecurringService::class)->addFrom($expiresAt, $service->billing)->subDays(setting('core.services.days_before_creation_renewal_invoice'));
        $months_label = "{$months} month";
        if ($months > 1){
            $months_label .= 's';
        }
        if ($months == 0.5){
            $months_label = '1 week';
        }
        $invoice->items()->create([
            'invoice_id' => $invoice->id,
            'name' => $service->getInvoiceName(),
            'description' => "Add extra {$months_label} to service #{$service->id} ({$service->name})",
            'quantity' => 1,
            'unit_price' => $price,
            'unit_setupfees' => 0,
            'total' => $total,
            'tax' => $tax,
            'subtotal' => $price,
            'setupfee' => 0,
            'type' => 'renewal',
            'related_id' => $service->id,
            'data' => ['months' => $months],
            'unit_original_price' => $service->price,
            'unit_original_setupfees' => 0,
        ]);

        ServiceRenewals::insert([
            'service_id' => $service->id,
            'invoice_id' => $invoice->id,
            'start_date' => $current,
            'end_date' => $expiresAt,
            'period' => $service->getAttribute('renewals') + 1,
            'next_billing_on' => $nextBilling,
            'created_at' => Carbon::now(),
        ]);
        $invoice->recalculate();
    }

    public static function appendProductOnExistingInvoice(AddProductToInvoiceDTO $dto)
    {
        $dto->invoice->items()->create($dto->toArray());
        $dto->invoice->recalculate();
    }

    public static function appendCouponOnExistingInvoice(Invoice $invoice, InvoiceItem $item, Coupon $coupon)
    {
        $item->discount = $item->getDiscountArray();
    }

    public static function getBillingType()
    {
        if (setting('billing_mode') == 'invoice'){
            return self::INVOICE;
        }
        return self::PRO_FORMA;
    }

}
