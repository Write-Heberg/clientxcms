<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('layouts/front')
@section('title', $product->name)
@section('scripts')
    <script src="{{ Vite::asset('resources/themes/default/js/basket.js') }}" type="module"></script>
    @endsection
@section('content')

    <div class="max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
        <form method="POST" action="{{ route('front.store.basket.config', ['product' => $product]) }}">
            <input type="hidden" name="currency" value="{{ $row->currency }}" id="currency">
            @csrf
            @include("shared.alerts")
            <h1 class="text-2xl font-semibold mb-4">{{ __('store.config.title') }}</h1>
            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-3 md:col-span-2">
                    @php($pricings = $product->pricingAvailable(currency()))
                    <div class="grid sm:grid-cols-3 gap-2 card" id="basket-billing-section">
                        <div class="col-span-3">
                            <h2 class="text-lg font-semibold mb-4">{{ __('store.config.billing') }}</h2>
                        </div>
                        @foreach ($pricings as $pricing)
                            <label for="billing-{{ $pricing->recurring }}-{{ $pricing->currency }}" class="col-span-3 md:col-span-1 flex p-3 block w-full bg-white border border-gray-200 rounded-lg text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400">
                                <span class="dark:text-gray-400 font-semibold">@if ($pricing->isFree()){{ __('global.free') }} @else {{ $pricing->recurringPayment() }} {{ $pricing->getSymbol() }} @endif {{ $pricing->recurring()['translate'] }}<p class="text-gray-500">{{ $pricing->pricingMessage() }} @if ($pricing->hasDiscountOnRecurring($product->getFirstPrice()))<span class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-teal-100 text-teal-800 dark:bg-teal-800/30 dark:text-teal-500">-{{ $pricing->getDiscountOnRecurring($product->getFirstPrice()) }}%</span>@endif</p></span>
                                <input type="radio" name="billing" value="{{ $pricing->recurring }}" {{ $billing == $pricing->recurring ? 'checked' : '' }} data-pricing="{{ $pricing->toJson()  }}" class="shrink-0 ms-auto mt-0.5 border-gray-200 rounded-full text-indigo-600 focus:ring-indigo-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-gray-800 dark:border-gray-700 dark:checked:bg-indigo-600 dark:checked:border-indigo-600 dark:focus:ring-offset-gray-800" id="billing-{{ $pricing->recurring }}-{{ $pricing->currency }}">
                            </label>
                        @endforeach
                    </div>
                    @if (!empty($data_html))
                        <div class="card border-b border-gray-900/10 pb-6">

                        {!! $data_html !!}
                        </div>
                        @endif
                </div>
                <div class="col-span-3 md:col-span-1">
                    <div class="card dark:text-gray-400">
                        <h2 class="text-lg font-semibold  dark:text-gray-300 mb-4">{{ __('store.config.summary') }}</h2>
                        <div class="flex justify-between mb-2">
                            <span>{{ __('global.product') }}</span>
                            <span>{{ $row->product->name }}</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span>{{ __('store.subtotal') }}</span>
                            <span id="subtotal">0</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span>{{ __('store.config.recurring_payment') }}</span>
                            <span id="recurring">0</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span>{{ __('store.vat') }}</span>
                            <span id="taxes">0</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span>{{ __('store.fees') }}</span>
                            <span id="fees">0</span>
                        </div>
                        <hr class="my-2">
                        <div class="flex justify-between mb-2">
                            <span class="font-semibold">{{ __('store.total') }}</span>
                            <span class="font-semibold" id="total">0</span>
                        </div>
                        <button class="bg-indigo-600 text-white py-2 px-4 rounded-lg mt-4 w-full">{{ __('store.basket.addtocart') }}</button>
                    </div>
            </div>
            </div>
        </form>
    </div>


@endsection
