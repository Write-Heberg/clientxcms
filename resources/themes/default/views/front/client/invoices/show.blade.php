<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('layouts/client')
@section('title', __('client.invoices.details'))
@section('content')
    <div class="max-w-[85rem] py-5 lg:py-7 mx-auto">
    <div class="sm:w-11/12 lg:w-3/4 mx-auto">
        @include('shared/alerts')

        <div class="card">
                <div class="flex justify-between">
                    <div>
                        <img class="mx-auto h-12 w-auto mt-4" src="{{ setting('app_logo_text') }}" alt="{{ setting('app_name') }}">

                    </div>

                    <div class="text-end">
                        <h2 class="text-2xl md:text-3xl font-semibold text-gray-800 dark:text-gray-200">{{ __('global.invoice') }} #</h2>
                        <span class="mt-1 block text-gray-500">{{ $invoice->identifier() }}</span>

                        <address class="mt-4 not-italic text-gray-800 dark:text-gray-200">
                            {!! nl2br(setting('app_address')) !!}
                        </address>
                    </div>
                </div>

                <div class="mt-8 grid sm:grid-cols-2 gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ __('client.invoices.billto', ['name' => $customer->firstname . ' ' . $customer->lastname]) }}</h3>
                        <address class="mt-2 not-italic text-gray-500">
                            {{ $customer->email }}<br>
                            {{ $customer->address }} {{ $customer->address2 != null ? ',' . $customer->address2 : '' }}<br>
                            {{ $customer->region }}, {{ $customer->city }} , {{ $customer->zipcode }}<br>
                            {{ $countries[$customer->country] }}<br>
                        </address>
                    </div>

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
                            <div class="text-end text-xs font-medium text-gray-500 uppercase">{{ __('store.price') }}</div>
                        </div>
                        @foreach ($invoice->items as $item)
                        <div class="hidden sm:block border-b border-gray-200 dark:border-gray-700"></div>

                        <div class="grid grid-cols-1 sm:grid-cols-6 gap-2">
                            <div class="sm:col-span-2 sm:flex">
                                <h5 class="sm:hidden text-xs font-medium text-gray-500 uppercase">{{ __('client.invoices.itemname') }}</h5>
                                <p class="font-medium text-gray-800 dark:text-gray-200">{{ $item->name }}</p>
                                @if ($item->canDisplayDescription())
                                <span class="font-medium text-gray-500 dark:text-gray-200">{{ $item->description }}</span>
                                @endif
                                @if ($item->getDiscount(false) != null)
                                    <span class="font-medium text-gray-400 text-start">{{ $item->getDiscountLabel() }}</span>
                                @endif
                            </div>
                            <div>
                                <h5 class="sm:hidden text-xs font-medium text-gray-500 uppercase">{{ __('client.invoices.qty') }}</h5>
                                <p class="text-gray-800 dark:text-gray-200">{{ $item->quantity }}</p>
                            </div>
                            <div>
                                <h5 class="sm:hidden text-xs font-medium text-gray-500 uppercase">{{ __('store.unit_price') }}</h5>
                                <div class="block">
                                    <p class="text-gray-800 dark:text-gray-200 text-end sm:text-start">{{ formatted_price($item->unit_price, $invoice->currency) }}</p>
                                    @if ($item->getDiscount() != null && $item->getDiscount(true)->discount_price > 0)
                                    <p class="font-medium text-gray-400 text-text-start sm:text-end">-{{ formatted_price($item->getDiscount()->discount_unit_price, $invoice->currency) }}</p>
                                        @endif
                                </div>
                            </div>
                            <div>
                                <h5 class="sm:hidden text-xs font-medium text-gray-500 uppercase">{{ __('store.setup_price') }}</h5>
                                <div class="block">
                                    <p class="text-gray-800 dark:text-gray-200 text-start">{{ formatted_price($item->unit_setupfees, $invoice->currency) }}</p>
                                    @if ($item->getDiscount() != null && $item->getDiscount(true)->discount_setup > 0)
                                        <p class="font-medium text-gray-400 text-text-start sm:text-end">-{{ formatted_price($item->getDiscount()->discount_unit_setup, $invoice->currency) }}</p>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <h5 class="sm:hidden text-xs font-medium text-gray-500 uppercase">{{ __('store.price') }}</h5>
                                <div class="block">
                                    <p class="text-gray-800 dark:text-gray-200 text-text-start sm:text-end">{{ formatted_price($item->price(), $invoice->currency) }}</p>
                                    @if ($item->getDiscount() != null && $item->getDiscount(true)->discount_setup > 0 || $item->getDiscount()->discount_price > 0)
                                        <p class="font-medium text-gray-400 text-text-start sm:text-end">-{{ formatted_price($item->getDiscount()->discount_price + $item->getDiscount()->discount_setup, $invoice->currency) }}</p>
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
                                <p class="sm:text-end font-semibold text-gray-800 dark:text-gray-200 sm:text-end text-start">{{ __('store.total') }}</p>
                            </div>

                            <div>
                                <h5 class="sm:hidden text-xs font-medium text-gray-500 uppercase">{{ __('store.total') }}</h5>

                                <p class="sm:text-end text-gray-800 dark:text-gray-200 sm:text-end sm:text-end text-start">{{ formatted_price($invoice->total, $invoice->currency) }}</p>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- End Table -->

                <!-- Flex -->
                <div class="mt-8 flex sm:justify-end">
                    <div class="w-full max-w-2xl sm:text-end space-y-2 text-end">
                        <!-- Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-1 gap-3 sm:gap-2">
                            @if ($invoice->canPay())
                            <dl class="grid sm:grid-cols-5 gap-x-3">
                                <dt class="col-span-5">
                                    <div class="hs-dropdown relative inline-flex">

                                        <button class="hs-dropdown-toggle py-2 px-3 inline-flex items-center rounded-lg gap-x-2 text-sm font-semibold border border-transparent bg-indigo-100 text-indigo-800 hover:bg-indigo-200 disabled:opacity-50 disabled:pointer-events-none">
                                            <svg class="flex-shrink-0 w-4 h-4"  xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M7 15h0M2 9.5h20"/></svg>
                                            {{ __('client.invoices.pay') }}
                                            <svg class="hs-dropdown-open:rotate-180 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>

                                        </button>

                                        <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-[15rem] bg-white shadow-md rounded-lg p-2 mt-2 dark:bg-gray-800 dark:border dark:border-gray-700 dark:divide-gray-700 after:h-4 after:absolute after:-bottom-4 after:start-0 after:w-full before:h-4 before:absolute before:-top-4 before:start-0 before:w-full" aria-labelledby="hs-dropdown-default">
                                            @foreach ($gateways as $gateway)
                                            <a class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300 dark:focus:bg-gray-700" href="{{ route('front.invoices.pay', ['invoice' => $invoice, 'gateway' => $gateway->uuid]) }}">
                                                {{ $gateway->getGatewayName() }}
                                            </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </dt>
                            </dl>
                                @endif
                        </div>
                    </div>
                </div>

                <div class="mt-8 sm:mt-12">

                    @if (!empty(setting("invoice_terms")))
                        <h6 class="text-md font-semibold text-gray-800 dark:text-gray-200">{{ __('client.invoices.terms') }}</h6>
                        <p class="text-gray-500 mb-3">{!! nl2br(setting("invoice_terms", "You can change this details in Invoice configuration.")) !!}</p>
                    @endif
                    @if ($invoice->paymethod == 'bank_transfert' && $invoice->status != 'paid')
                        <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ __('client.invoices.banktransfer.title') }}</h4>
                        <p class="text-gray-500">{!! nl2br(setting("bank_transfert_details", "You can change this details in Bank transfer configuration.")) !!}</p>
                        @elseif ($invoice->status == 'paid')
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ __('client.invoices.thank') }}</h4>
                    <p class="text-gray-500">{{ __('client.invoices.thankmessage') }}</p>
                        @endif

                </div>

                <p class="mt-5 text-sm text-gray-500">© {{ date('Y') }} {{ config('app.name') }}.</p>
            </div>

            <div class="mt-6 flex justify-end gap-x-3 print:hidden">
                <a class="py-2 px-3 inline-flex justify-center items-center gap-2 rounded-lg border font-medium bg-white text-gray-700 shadow-sm align-middle hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white focus:ring-blue-600 transition-all text-sm dark:bg-gray-800 dark:hover:bg-slate-800 dark:border-gray-700 dark:text-gray-400 dark:hover:text-white dark:focus:ring-offset-gray-800" href="{{ route('front.invoices.download', ['invoice' => $invoice]) }}">
                    <svg class="flex-shrink-0 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                    {{ __('client.invoices.download') }}
                </a>
                <a onclick="window.print();" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50 disabled:pointer-events-none dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600" href="#">
                    <svg class="flex-shrink-0 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                    {{ __('client.invoices.print') }}
                </a>
            </div>
        </div>
    </div>
@endsection
