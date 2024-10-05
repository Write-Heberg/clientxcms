<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin\Core;

use App\DTO\Admin\Invoice\AddCouponToInvoiceItemDTO;
use App\DTO\Admin\MassActionDTO;
use App\DTO\Store\ProductDataDTO;
use App\Events\Core\Invoice\InvoiceCreated;
use App\Helpers\Countries;
use App\Http\Controllers\Admin\AbstractCrudController;
use App\Http\Requests\Admin\Invoice\InvoiceDraftRequest;
use App\Models\Account\Customer;
use App\Models\Core\Gateway;
use App\Models\Core\Invoice;
use App\Models\Core\InvoiceItem;
use App\Models\Provisioning\Service;
use App\Models\Store\Coupon;
use App\Models\Store\Product;
use Illuminate\Http\Request;

class InvoiceController extends AbstractCrudController
{
    protected string $viewPath = 'admin.core.invoices';
    protected string $routePath = 'admin.invoices';
    protected string $translatePrefix = 'admin.invoices';
    protected string $model = Invoice::class;
    protected int $perPage = 25;
    protected string $searchField = 'email';


    public function getIndexFilters()
    {
        return collect(Invoice::FILTERS)->merge([Invoice::STATUS_DRAFT => Invoice::STATUS_DRAFT])->mapWithKeys(function($k, $v) {
            return [$k => __('global.states.' . $v)];
        })->toArray();
    }

    public function getSearchFields()
    {
        return [
            'id' => 'ID',
            'customer.email' => __('global.customer'),
            'invoice_number' => __('admin.invoices.invoice_number'),
            'external_id' => __('admin.invoices.show.external_id'),
        ];
    }

    public function getMassActions()
    {
        return [
            new MassActionDTO('delete', __('global.delete'), function (Invoice $invoice) {
                $invoice->items()->delete();
                $invoice->delete();
            }),
            new MassActionDTO('complete', __('admin.invoices.mass.complete'), function (Invoice $invoice) {
                $invoice->complete(false);
            }),
            new MassActionDTO('cancel', __('admin.invoices.mass.cancel'), function (Invoice $invoice) {
                $invoice->cancel();
            }),
            new MassActionDTO('refund', __('admin.invoices.mass.refund'), function (Invoice $invoice) {
                $invoice->refund();
            }),
            new MassActionDTO('fail', __('admin.invoices.mass.fail'), function (Invoice $invoice) {
                $invoice->fail();
            }),
        ];
    }

    public function store(Request $request)
    {
        $this->checkPermission('create');
        $validatedData = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'currency' => 'required|string|max:3',
            'date_due' => 'required|date',
        ]);
        $invoice = Invoice::create([
            'customer_id' => $validatedData['customer_id'],
            'status' => Invoice::STATUS_DRAFT,
            'currency' => $validatedData['currency'],
            'due_date' => $validatedData['date_due'],
            'total' => 0,
            'subtotal' => 0,
            'tax' => 0,
            'setupfees' => 0,
            'discount' => [],
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'notes' => 'Created manually by ' . auth('admin')->user()->username,
        ]);
        return $this->storeRedirect($invoice);
    }

    public function deliver(Invoice $invoice, InvoiceItem $item)
    {
        try {
            $item->tryDeliver();
            return back()->with('success', __('admin.invoices.deliveredsuccess'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
    public function draft(Invoice $invoice, InvoiceDraftRequest $request)
    {
        $this->checkPermission('update');
        $validatedData = $request->validated();
        $related = $validatedData['related'];
        $relatedId = $validatedData['related_id'];
        if ($related == 'service'){
            $service = Service::find($relatedId);
            $invoice->addService($service);
        } else if ($related == 'product'){
            $product = Product::find($relatedId);
            if ($product->productType()->data($product) != null){
                $productData = \Validator::validate($request->all(),$product->productType()->data($product)->validate());
            } else {
                $productData = [];
            }
            $invoice->addProduct($product, $validatedData, $productData);
        } else {
            $validatedData['description'] = $validatedData['description'] ?? '';
            $invoice->addCustomProduct($validatedData);
        }
        $invoice->recalculate();
        return back()->with('success', __('admin.invoices.draft.itemadded'));
    }

    public function deleteItem(InvoiceItem $invoiceItem)
    {
        $this->checkPermission('update');
        if (!$invoiceItem->invoice->isDraft()) {
            return back()->with('error', __('admin.invoices.draft.notallowed'));
        }
        $invoiceItem->delete();
        $invoiceItem->invoice->recalculate();
        return back()->with('success', __('admin.invoices.draft.itemremoved'));
    }

    public function updateItem(InvoiceItem $invoiceItem, Request $request)
    {
        $this->checkPermission('update');
        if (!$invoiceItem->invoice->isDraft()) {
            return back()->with('error', __('admin.invoices.draft.notallowed'));
        }
        $validatedData = $request->validate([
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'unit_setupfees' => 'required|numeric|min:0',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'coupon_id' => 'nullable',
        ]);
        if ($invoiceItem->relatedType() instanceof Product) {
            $product = $invoiceItem->relatedType();
            if ($invoiceItem->relatedType()->productType()->data($product) != null){
                $productData = \Validator::validate($request->all(),$invoiceItem->relatedType()->productType()->data($product)->validate());
                $validatedData['data'] = $invoiceItem->relatedType()->productType()->data($product)->parameters(new ProductDataDTO($invoiceItem->relatedType(), $invoiceItem->data, $productData));
            } else {
                $validatedData['data'] = [];
            }
        }
        if (array_key_exists('billing', $validatedData)) {
            $validatedData['data']['billing'] = $validatedData['billing'];
        }
        $validatedData['description'] = $validatedData['description'] ?? '';
        $validatedData['unit_original_price'] = $validatedData['unit_price'];
        $validatedData['unit_original_setupfees'] = $validatedData['unit_setupfees'];
        if (array_key_exists('coupon_id', $validatedData)) {
            /** @var Coupon $coupon */
            $coupon = Coupon::find($validatedData['coupon_id']);
            if ($coupon != null) {
                $validatedData['discount'] = $coupon->discountArray(new AddCouponToInvoiceItemDTO($validatedData, $invoiceItem, $product ?? null));
            } else {
                $validatedData['discount'] = [];
            }
        } else {
            $validatedData['discount'] = [];
        }
        $invoiceItem->update($validatedData);
        $invoiceItem->invoice->recalculate();
        return back()->with('success', __('admin.invoices.draft.itemupdated'));
    }


    public function show(Invoice $invoice)
    {
        $this->checkPermission('show');
        $params['item'] = $invoice;
        $params['invoice'] = $invoice;
        $params['customer'] = $invoice->customer;
        $params['countries'] = Countries::names();
        $params['gateways'] = $this->gateways();
        if ($invoice->isDraft()) {
            $params['products'] = $this->products($invoice);
            $params['coupons'] = $this->coupons();
        }
        return $this->showView($params);
    }

    public function config(Invoice $invoice, Request $request)
    {
        $this->checkPermission('update');
        $relatedId = $request->get('related_id');
        $related = $request->get('related');
        $routePath = $this->routePath;
        $translatePrefix = $this->translatePrefix;
        $billing = 'monthly';
        if ($related == 'service'){
            /** @var Product|null $product */
            $service = Service::find($relatedId);
            $product = $service->product;
            $dataHTML = '';
        } else if ($related == 'product'){
            /** @var Product|null $product */
            $product = Product::find($relatedId);
            $service = null;
            if ($product != null && $product->productType()->data($product) != null){
                $dataHTML = $product->productType()->data($product)->render(new ProductDataDTO($product, $request->all() + ['in_admin' => true], [], []));
            } else {
                $dataHTML = '';
            }
        } else {
            // Autre cas de figures
            $product = null;
            $service = null;
            $dataHTML = '';
        }
        if ($product != null){
            $available = $product->pricingAvailable(currency());
            if ($product->getPriceByCurrency(currency(), $billing)->price == 0 && count($available) > 0) {
                $billing = $available[0]->recurring;
            }
        }
        $coupons = $this->coupons();
        return view($this->viewPath . '.config', compact('coupons', 'relatedId', 'related', 'service','billing','invoice', 'translatePrefix', 'routePath','product', 'dataHTML'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->checkPermission('update');
        $validatedData = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Invoice::getStatuses())),
            'notes' => 'required|string|max:255',
            'paymethod' => 'string|max:255',
            'fees' => 'numeric|min:0',
            'tax' => 'numeric|min:0',
            'currency' => 'required|string|max:3',
            'external_id' => ['nullable', 'string', 'max:255', 'unique:invoices,external_id,' . $invoice->id],
        ]);
        if ($validatedData['status'] != $invoice->status) {
            if ($validatedData['status'] == Invoice::STATUS_PAID) {
                $invoice->complete(false);
            }
            if ($validatedData['status'] == Invoice::STATUS_CANCELLED) {
                $invoice->cancel();
            }
            if ($validatedData['status'] == Invoice::STATUS_REFUNDED) {
                $invoice->refund();
            }
            if ($validatedData['status'] == Invoice::STATUS_FAILED) {
                $invoice->fail();
            }
        }
        $invoice->update($validatedData);
        return $this->updateRedirect($invoice);
    }

    public function search(Request $request)
    {
        $q = $request->get('q');
        if (filter_var($q, FILTER_VALIDATE_EMAIL)) {
            $customer = Customer::select('id')->where('email', $q)->first();
            return $this->model::where('customer_id', $customer->id)->paginate($this->perPage);
        }
        return $this->model::where('id', $q)->paginate($this->perPage);
    }

    public function pdf(Invoice $invoice)
    {
        $this->checkPermission('show');
        return $invoice->pdf();
    }

    public function getCreateParams()
    {
        $params = parent::getCreateParams();
        $params['customers'] = $this->customers();
        $params['currencies'] = collect(currencies())->mapWithKeys(function($currency) {
            return [$currency['code'] => $currency['code']];
        })->toArray();
        $params['date_due'] = now()->addDays(30)->format('Y-m-d');
        return $params;
    }

    public function destroy(Invoice $invoice)
    {
        $this->checkPermission('delete');
        $invoice->items()->delete();
        $invoice->delete();
        return $this->deleteRedirect($invoice);
    }

    public function validateInvoice(Invoice $invoice)
    {
        $this->checkPermission('create');
        if ($invoice->status != Invoice::STATUS_DRAFT) {
            return back()->with('error', __('admin.invoices.draft.validated'));
        }
        if ($invoice->items->count() == 0) {
            return back()->with('error', __('admin.invoices.draft.empty'));
        }
        $invoice->status = Invoice::STATUS_PENDING;
        $invoice->save();
        event(new InvoiceCreated($invoice));
        return back()->with('success', __('admin.invoices.draft.validated'));
    }


    private function customers()
    {
        return Customer::select(['id', 'email', 'firstname', 'lastname'])->get()->mapWithKeys(function(Customer $customer) {
            return [$customer->id => $customer->email];
        });
    }


    private function products(Invoice $invoice)
    {
        $products = Product::getAvailable(true)->pluck('name', 'id')->mapWithKeys(function ($name, $id) {
            return ["product-".$id => $name];
        });
        foreach (Service::where('customer_id', $invoice->customer_id)->whereNotNull('expires_at')->get() as $service) {
            $products->put("service-".$service->id, " #" . $service->id . ' ' . $service->getInvoiceName());
        }
        $products->put('product-none', __('admin.invoices.customproduct'));
        return $products;
    }

    private function gateways()
    {
        return Gateway::getAvailable(true)->pluck('name', 'uuid')->mapWithKeys(function ($name, $uuid) {
            return [$uuid => $name];
        })->toArray();
    }

    private function coupons()
    {
        $coupons = Coupon::all();
        $coupons = $coupons->pluck('code', 'id')->mapWithKeys(function ($name, $id) {
            return [$id => $name];
        });
        $coupons->put('none', __('global.none'));
        return $coupons;
    }
}
