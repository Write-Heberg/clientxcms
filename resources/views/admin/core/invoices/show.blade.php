<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin/layouts/admin')
@section('title',  __($translatePrefix . '.show.title', ['id' => $item->identifier()]))
@section('scripts')
    <script src="{{ Vite::asset('resources/global/js/clipboard.js') }}" type="module"></script>
    <script src="{{ Vite::asset('resources/global/js/flatpickr.js') }}" type="module"></script>
    <script src="{{ Vite::asset('resources/global/js/admin/invoicedraft.js') }}" type="module"></script>
@endsection
@section('content')

            <div class="flex flex-col md:flex-row gap-4">
                <div class="md:w-2/3">
                    @if ($invoice->isDraft() && !empty($errors))
                        @php
                            Session::flash('error', collect($errors->all())->map(function ($error) {
                                return $error;
                            })->implode('<br>'));
                        @endphp
                    @endif
                    @include('admin/shared/alerts')
                    <div class="card">
                                <div class="flex justify-between">
                                    <div>
                                        <img class="mx-auto h-12 w-auto mt-4" src="{{ setting('app_logo_text') }}" alt="{{ setting('app_name') }}">

                                    </div>
                                    <!-- Col -->

                                    <div class="text-end">
                                        <h2 class="text-2xl md:text-3xl font-semibold text-gray-800 dark:text-gray-200">{{ __('global.invoice') }} #</h2>
                                        <span class="mt-1 block text-gray-500">{{ $invoice->identifier() }}</span>

                                        <address class="mt-4 not-italic text-gray-800 dark:text-gray-200">
                                            {!! nl2br(setting('app_address')) !!}
                                        </address>
                                    </div>
                                </div>

                                <div class="mt-8 grid sm:grid-cols-2 gap-3">
                                    <a href="{{ route('admin.customers.show', ['customer' => $customer]) }}" target="_blank">
                                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ __('client.invoices.billto', ['name' => $customer->firstname . ' ' . $customer->lastname]) }}</h3>
                                        <address class="mt-2 not-italic text-gray-500">
                                            {{ $customer->email }}<br>
                                            {{ $customer->address }} {{ $customer->address2 != null ? ',' . $customer->address2 : '' }}<br>
                                            {{ $customer->region }}, {{ $customer->city }} , {{ $customer->zipcode }}<br>
                                            {{ $countries[$customer->country] }}<br>
                                        </address>
                                    </a>

                                    <div class="space-y-2">
                                        <div class="grid grid-cols-2 sm:grid-cols-1 gap-3 sm:gap-2">
                                            <dl class="grid sm:grid-cols-5 gap-x-3">
                                                <dt class="col-span-3 font-semibold text-gray-800 dark:text-gray-200">{{ __('client.invoices.invoice_date') }}:</dt>
                                                <dd class="col-span-2 text-gray-500">{{ $invoice->created_at->format('d/m/y H:i') }}</dd>
                                            </dl>

                                            <dl class="grid sm:grid-cols-5 gap-x-3">
                                                <dt class="col-span-3 font-semibold text-gray-800 dark:text-gray-200">{{ __('client.invoices.due_date') }}:</dt>
                                                <dd class="col-span-2 text-gray-500">{{ $invoice->due_date->format('d/m/y H:i') }}</dd>
                                            </dl>

                                            <dl class="grid sm:grid-cols-5 gap-x-3">
                                                <dt class="col-span-3 font-semibold text-gray-800 dark:text-gray-200">{{ __('global.status') }}:</dt>
                                                <dd class="col-span-2 text-gray-500"><x-badge-state state="{{ $invoice->status }}"></x-badge-state></dd>
                                            </dl>
                                            @if ($invoice->paymethod != null)
                                            <dl class="grid sm:grid-cols-5 gap-x-3">
                                                <dt class="col-span-3 font-semibold text-gray-800 dark:text-gray-200">{{ __('client.invoices.paymethod') }}:</dt>
                                                <dd class="col-span-2 text-gray-500">{{ $invoice->gateway != null ? $invoice->gateway->name : $invoice->paymethod }}</dd>
                                            </dl>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <div class="border border-gray-200 p-4 rounded-lg space-y-4 dark:border-gray-700">
                                        <div class="hidden sm:grid sm:grid-cols-6">
                                            <div class="sm:col-span-2 text-xs font-medium text-gray-500 uppercase">{{ __('client.invoices.itemname')  }}</div>
                                            <div class="text-start text-xs font-medium text-gray-500 uppercase">{{ __('client.invoices.qty') }}</div>
                                            <div class="text-start text-xs font-medium text-gray-500 uppercase">{{ __('store.unit_price') }}</div>
                                            <div class="text-start text-xs font-medium text-gray-500 uppercase">{{ __('store.setup_price') }}</div>
                                            <div class="text-center text-xs font-medium text-gray-500 uppercase">{{ __('store.price') }}</div>
                                        </div>

                                        @if ($invoice->items->isEmpty())
                                            <tr class="bg-white hover:bg-gray-50 dark:bg-slate-900 dark:hover:bg-slate-800">
                                                <td colspan="9" class="px-6 py-4 whitespace-nowrap text-center">
                                                    <div class="flex flex-auto flex-col justify-center items-center p-2 md:p-3">
                                                        <p class="text-sm text-gray-800 dark:text-gray-400">
                                                            {{ __('global.no_results') }}
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                        @foreach ($invoice->items as $item)
                                            <div class="hidden sm:block border-b border-gray-200 dark:border-gray-700"></div>

                                            <div class="grid grid-cols-1 sm:grid-cols-6 gap-2">
                                                <div class="sm:col-span-2 sm:flex">

                                                    <h5 class="sm:hidden text-xs font-medium text-gray-500 uppercase">{{ __('client.invoices.itemname') }}</h5>


                                                    @if ($invoice->isDraft())

                                                        <form method="POST" class="flex" action="{{ route($routePath . '.deleteitem', ['invoice_item' => $item]) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="text-danger mx-2" type="submit">
                                                                <i class="bi bi-trash"></i>
                                                                <span class="sr-only">{{ __('global.delete') }}</span>
                                                            </button>

                                                            <button type="button" id="btn-edit-{{ $item->id }}" class="text-warning mx-2" data-hs-overlay="#edititem-{{ $item->id }}">
                                                                <i class="bi bi-pencil mr-2"></i>
                                                                <span class="sr-only">{{ __('global.edit') }}</span>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <div>
                                                        @if ($invoice->status == $invoice::STATUS_PAID && $item->delivered_at == null)
                                                            <form method="POST" class="flex" action="{{ route($routePath . '.deliver', ['item' => $item, 'invoice' => $invoice]) }}">
                                                                @csrf
                                                                <p class="font-medium text-gray-800 dark:text-gray-200">{{ $item->name }}</p>
                                                                @if ($item->canDisplayDescription())
                                                                    <span class="font-medium text-gray-500 dark:text-gray-200">{{ $item->description }}</span>
                                                                @endif
                                                                @if ($item->getDiscount(false) != null)
                                                                    <span class="font-medium text-gray-400 text-start">{{ $item->getDiscountLabel() }}</span>
                                                                @endif

                                                                <button class="btn btn-primary btn-sm ml-3">
                                                                    <i class="bi bi-box mr-2"></i>
                                                                    {{ __('client.invoices.deliver') }}</button>
                                                            </form>
                                                        @else
                                                            <p class="font-medium text-gray-800 dark:text-gray-200">{{ $item->name }}</p>
                                                            @if ($item->canDisplayDescription())
                                                                <span class="font-medium text-gray-500 dark:text-gray-200">{{ $item->description }}</span>
                                                            @endif
                                                            @if ($item->getDiscount(false) != null)
                                                                <span class="font-medium text-gray-400 text-start">{{ $item->getDiscountLabel() }}</span>
                                                            @endif
                                                        @endif

                                                    </div>
                                                        </div>
                                                        <div>
                                                            <h5 class="sm:hidden text-xs font-medium text-gray-500 uppercase">{{ __('client.invoices.qty') }}</h5>
                                                            <p class="text-gray-800 dark:text-gray-200">{{ $item->quantity }}</p>
                                                        </div>
                                                        <div>
                                                            <h5 class="sm:hidden text-xs font-medium text-gray-500 uppercase">{{ __('store.unit_price') }}</h5>
                                                            <div class="block">
                                                                <p class="text-gray-800 dark:text-gray-200 text-start">{{ formatted_price($item->unit_price, $invoice->currency) }}</p>
                                                                @if ($item->getDiscount() != null && $item->getDiscount()->discount_price > 0)
                                                                    <p class="font-medium text-gray-400 text-start sm:text-end">-{{ formatted_price($item->getDiscount()->discount_unit_price, $invoice->currency) }}</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h5 class="sm:hidden text-xs font-medium text-gray-500 uppercase">{{ __('store.setup_price') }}</h5>
                                                            <div class="block">
                                                                <p class="text-gray-800 dark:text-gray-200 text-start">{{ formatted_price($item->unit_setupfees, $invoice->currency) }}</p>
                                                                @if ($item->getDiscount() != null && $item->getDiscount()->discount_setup > 0)
                                                                    <p class="font-medium text-gray-400 text-start sm:text-end">-{{ formatted_price($item->getDiscount()->discount_unit_setup, $invoice->currency) }}</p>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div>
                                                            <h5 class="sm:hidden text-xs font-medium text-gray-500 uppercase">{{ __('store.price') }}</h5>
                                                            <div class="block">
                                                                <p class="text-gray-800 dark:text-gray-200 text-start sm:text-end">{{ formatted_price($item->price(), $invoice->currency) }}</p>
                                                                @if ($item->getDiscount() != null && $item->getDiscount()->discount_setup > 0 || $item->getDiscount()->discount_price > 0)
                                                                    <p class="font-medium text-gray-400 text-start sm:text-end">-{{ formatted_price($item->getDiscount()->discount_price + $item->getDiscount()->discount_setup, $invoice->currency) }}</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                        @endforeach

                                        <div class="hidden sm:block border-b border-gray-200 dark:border-gray-700"></div>
                                        <div class="grid grid-cols-1 sm:grid-cols-6 gap-2">
                                            <div class="sm:col-span-5 hidden sm:grid">
                                                <p class="sm:text-end font-semibold text-gray-800 dark:text-gray-200 text-end">{{ __('store.subtotal') }}</p>
                                            </div>

                                            <div>
                                                <h5 class="sm:hidden text-xs font-medium text-gray-500 uppercase">{{ __('store.subtotal') }}</h5>

                                                <p class="sm:text-end text-gray-800 dark:text-gray-200 sm:text-end text-start">{{ formatted_price($invoice->subtotal, $invoice->currency) }}</p>
                                            </div>

                                        </div>

                                        <div class="hidden sm:block border-b border-gray-200 dark:border-gray-700"></div>

                                        <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                                            <div class="sm:col-span-5 hidden sm:grid">
                                                <p class="sm:text-end font-semibold text-gray-800 dark:text-gray-200 text-end">{{ __('store.vat') }}</p>
                                            </div>

                                            <div>
                                                <h5 class="sm:hidden text-xs font-medium text-gray-500 uppercase">{{ __('store.vat') }}</h5>

                                                <p class="sm:text-end text-gray-800 dark:text-gray-200 sm:text-end text-start">{{ formatted_price($invoice->tax, $invoice->currency) }}</p>
                                            </div>

                                        </div>

                                        <div class="hidden sm:block border-b border-gray-200 dark:border-gray-700"></div>

                                        <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                                            <div class="col-span-5 hidden sm:grid">
                                                <p class="sm:text-end font-semibold text-gray-800 dark:text-gray-200">{{ __('store.transaction_fee') }}</p>
                                            </div>

                                            <div>
                                                <h5 class="sm:hidden text-xs font-medium text-gray-500 uppercase">{{ __('store.transaction_fee') }}</h5>

                                                <p class="sm:text-end text-gray-800 dark:text-gray-200 sm:text-end text-start">{{ formatted_price($invoice->fees, $invoice->currency) }}</p>
                                            </div>

                                        </div>
                                        <div class="hidden sm:block border-b border-gray-200 dark:border-gray-700"></div>

                                        <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                                            <div class="col-span-5 hidden sm:grid">
                                                <p class="sm:text-end font-semibold text-gray-800 dark:text-gray-200 sm:text-end text-start">{{ __('store.total') }}</p>
                                            </div>

                                            <div>
                                                <h5 class="sm:hidden text-xs font-medium text-gray-500 uppercase">{{ __('store.total') }}</h5>

                                                <p class="sm:text-end text-gray-800 dark:text-gray-200 sm:text-end sm:text-end text-start">{{ formatted_price($invoice->total, $invoice->currency) }}</p>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                @if ($invoice->isDraft())

                                    <div class="mt-8 grid sm:grid-cols-2 gap-3">
                                        <div>
                                        </div>

                                        <div class="space-y-2">
                                            <div class="grid grid-cols-2 sm:grid-cols-1 gap-3 sm:gap-2 flex ">
                                                @include('admin/shared/search-select', ['name' => 'product', 'label' => __($translatePrefix . '.draft.add'), 'options' => $products, 'value' => 1])
                                                <button class="btn btn-primary mt-2" id="add-item-btn" data-fetch="{{ route($routePath . '.config', ['invoice' => $invoice]) }}">{{ __('global.add') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if (!$invoice->isDraft())
                                <a class="btn-primary btn mt-2" href="{{ route($routePath . '.pdf', ['invoice' => $invoice]) }}">
                                    {{ __('client.invoices.download') }}
                                </a>
                                    @endif
                            </div>
                </div>

                <div class="md:w-1/3">
                    <div class="card">
                        <form method="POST" action="{{ route($routePath . '.update', ['invoice' => $invoice]) }}">
                            @csrf
                            @method('PUT')
                        @include('admin/shared/input', ['name' => 'external_id', 'label' => __('admin.invoices.show.external_id'), 'value' => $invoice->external_id])
                        @include('admin/shared/textarea', ['name' => 'notes', 'label' => __('admin.invoices.show.notes'), 'value' => $invoice->notes])

                            @include('admin/shared/select', ['name' => 'status', 'label' => __('global.status'), 'options' => $invoice::getStatuses(), 'value' => $invoice->status])
                            @include('admin/shared/select', ['name' => 'paymethod', 'label' => __('client.invoices.paymethod'), 'options' => $gateways, 'value' => $invoice->paymethod])
                            <div class="grid sm:grid-cols-2 gap-2">
                                <div>
                            @include('admin/shared/input', ['name' => 'fees', 'label' => __('store.transaction_fee'), 'value' => $invoice->fees, 'type' => 'number'])
                                </div>

                                <div>
                                    <label for="price" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-400 mt-2">{{ __('store.vat') }} / {{ __('global.currency') }}</label>
                                    <div class="relative mt-2">
                                        <input type="text" id="tax" name="tax" class="py-3 px-4 ps-9 pe-20 input-text" placeholder="0.00" value="{{ old('tax', $invoice->tax) }}">
                                        <div class="absolute inset-y-0 end-0 flex items-center text-gray-500 pe-px">
                                            <label for="currency" class="sr-only">{{ __('global.currency') }}</label>
                                            <select id="currency" name="currency" class="store w-full border-transparent rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:bg-gray-700 dark:border-gray-700 dark:text-gray-400">
                                                @foreach(currencies() as $currency)
                                                    <option value="{{ $currency['code'] }}" @if($currency['code'] == $invoice->currency) selected @endif>{{ $currency['code'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    @error('vat')
                                    <span class="mt-2 text-sm text-red-500">
            {{ $message }}
        </span>
                                    @enderror
                                    @error('currency')
                                    <span class="mt-2 text-sm text-red-500">
            {{ $message }}
        </span>
                                    @enderror
                                </div>
                                </div>
                            @if (!$invoice->isDraft())

                            <div>
                                <div class="flex rounded-lg shadow-sm mt-2">
                                    <input type="text" readonly class="input-text" id="invoice_url" value="{{ route('front.invoices.show', ['invoice' => $invoice->id]) }}">
                                    <button type="button" data-clipboard-target="#invoice_url" data-clipboard-action="copy" data-clipboard-success-text="Copied" class=" js-clipboard w-[2.875rem] h-[2.875rem] flex-shrink-0 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-e-md border border-transparent bg-blue-600 text-white hover:bg-blue-700  dark:focus:ring-1 dark:focus:ring-gray-600">
                                        <svg class="js-clipboard-default w-4 h-4 group-hover:rotate-6 transition" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/></svg>
                                        <svg class="js-clipboard-success hidden w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    </button>
                                </div>
                            </div>
                            @endif
                            @if (staff_has_permission('manage_invoices'))
                            <button class="btn btn-primary mt-2">{{ __('global.save') }}</button>
                            @endif
                            @if (!$invoice->isDraft())

                            <button class="btn btn-secondary text-left mt-2" type="button" data-hs-overlay="#metadata-overlay">
                                <i class="bi bi-database mr-2"></i>
                                {{ __('admin.metadata.title') }}
                            </button>
                                @endif
                        </form>
                    </div>

                    @if ($invoice->isDraft() && staff_has_permission('create_invoices'))
                        <form method="POST" action="{{ route($routePath . '.validate', ['invoice' => $invoice]) }}">
                            @csrf
                            <button class="btn btn-secondary w-full mt-2">{{ __($translatePrefix . '.draft.validatebtn') }}</button>
                        </form>
                    @endif
                </div>
            @include('admin/metadata/overlay', ['item' => $invoice, 'items' => collect([$invoice])->merge($invoice->items)])
    @if ($invoice->isDraft())
        @include('admin/core/invoices/draftoverlay', ['invoice' => $invoice])
@endif
@endsection
