<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin.settings.sidebar')
@section('title', __('admin.settings.store.checkout.title'))
@section('setting')
    <div class="card">
        <h4 class="font-semibold uppercase text-gray-600 dark:text-gray-400">
            {{ __('admin.settings.store.checkout.title')  }}
        </h4>
        <p class="mb-2 font-semibold text-gray-600 dark:text-gray-400">
            {{ __('admin.settings.store.checkout.description') }}
        </p>

        <form action="{{ route('admin.settings.store.billing.save') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-6 mt-4 sm:grid-cols-3">
                <div>
                    @include('shared/input', ['label' => __('admin.settings.store.checkout.fields.toslink'), 'name' => 'checkout_toslink', 'value' => setting('checkout_toslink')])
                </div>
                <div>
                    @include('shared/select', ['label' => __('admin.settings.store.checkout.fields.mode_tax.title'), 'name' => 'store_mode_tax', 'options' => $options, 'value' => setting('store_mode_tax')])
                </div>
                <div>
                    @include('shared/select', ['label' => __('admin.settings.store.checkout.fields.currency'), 'name' => 'store_currency', 'options' => $currencies, 'value' => setting('store_currency', 'EUR')])
                </div>
                <div class="col-span-2">
                    @include('shared/textarea', ['name'=> 'app_address', 'label' => __('admin.settings.core.app.fields.app_address'), 'value' => setting('app_address')])
                </div>
                <div>
                    @include('shared/textarea', ['name'=> 'invoice_terms', 'label' => __('admin.settings.core.app.fields.invoice_terms'), 'value' => setting('invoice_terms', 'You can change this details in Invoice configuration.')])
                </div>
                    <div>
                    @include('shared/select', ['label' => __('admin.settings.store.checkout.fields.billing_mode'), 'name' => 'billing_mode', 'value' => setting('billing_mode'), 'options' => $billing_modes])
                        <div class="mt-2">
                    @include('shared/checkbox', ['label' => __('admin.settings.store.checkout.fields.store_vat_enabled'), 'name' => 'store_vat_enabled', 'value' => setting('store_vat_enabled')])
                        </div>
                </div>

                <div>
                    @include('shared/input', ['label' => __('admin.settings.store.checkout.fields.invoice_prefix'), 'name' => 'billing_invoice_prefix', 'value' => setting('billing_invoice_prefix')])
                    <div class="mt-2">
                    @include('shared/checkbox', ['label' => __('admin.settings.store.checkout.fields.customermustbeconfirmed'), 'name' => 'checkout_customermustbeconfirmed', 'value' => setting('checkout_customermustbeconfirmed')])
                    </div>
                </div>

                <div>
                    <label for="remove_pending_invoice" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-400 mt-2">{{ __('admin.settings.store.checkout.fields.remove_pending_invoice') }}</label>
                    <div class="relative mt-2">
                        <div class="absolute inset-y-0 end-0 flex items-center text-gray-500 pe-px">
                            <label for="remove_pending_invoice_type" class="sr-only">{{ __('global.actions') }}</label>
                            <select id="remove_pending_invoice_type" name="remove_pending_invoice_type" class="store w-full border-transparent rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:bg-gray-700 dark:border-gray-700 dark:text-gray-400">
                                <option value="delete" @if(setting('remove_pending_invoice_type', 'cancel') == 'delete') selected @endif>{{ __('admin.settings.store.checkout.fields.remove_pending_invoice_types.delete') }}</option>
                                <option value="cancel" @if(setting('remove_pending_invoice_type', 'cancel') == 'cancel') selected @endif>{{ __('admin.settings.store.checkout.fields.remove_pending_invoice_types.cancel') }}</option>
                            </select>
                        </div>
                        <input type="text" id="remove_pending_invoice" name="remove_pending_invoice" class="py-3 px-4 ps-9 pe-20 input-text" placeholder="0.00" value="{{ setting('remove_pending_invoice', 0) }}">

                    </div>
                    <p class="text-sm text-gray-500 mt-2">
                        {{ __('admin.settings.store.checkout.fields.remove_pending_invoice_help') }}
                    </p>
                </div>
                <div class="col-span-2">
                    <div class="flex justify-between">

                    <h3 class="font-semibold uppercase text-gray-600 dark:text-gray-400">{{ __('admin.settings.store.checkout.webhookoncheckout') }}</h3>

                    <div class="hs-tooltip [--trigger:click]">
                        <div class="hs-tooltip-toggle block text-center">
                            <button type="button" class="inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent text-blue-600 hover:text-blue-800 disabled:opacity-50 disabled:pointer-events-none dark:text-blue-500 dark:hover:text-blue-400">
                                {{ __('global.preview') }}
                                <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m18 15-6-6-6 6"></path>
                                </svg>
                            </button>

                            <div class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible hidden opacity-0 transition-opacity absolute invisible z-10 max-w-xs w-full bg-white border border-gray-100 text-start rounded-xl shadow-md dark:bg-neutral-800 dark:border-neutral-700" role="tooltip">
                                <div class="p-4">
                                    <div class="mb-3 flex justify-between items-center gap-x-3">
                                        <img src="https://cdn.clientxcms.com/ressources/docs/order.png">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                    @include('shared/password', ['label' => __('webhook.url'), 'name' => 'store_checkout_webhook_url', 'value' => setting('store_checkout_webhook_url')])
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-2">{{ __('global.save') }}</button>
        </form>
@endsection
