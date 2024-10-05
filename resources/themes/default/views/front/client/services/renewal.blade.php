<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('layouts/client')
@section('title', __('client.services.renewals.index'))
@section('scripts')
    <script src="{{ Vite::asset('resources/themes/default/js/popupwindow.js') }}" type="module" defer></script>
@endsection
@section('content')
    <div class="max-w-[85rem] py-5 lg:py-7 mx-auto">
    <div class="sm:w-11/12 lg:w-3/4 mx-auto">
        <div class="flex flex-col md:flex-row gap-4">

        <div class="md:w-3/4">
            @include('shared/alerts')

            <div class="card">
                <div class="card-heading">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                            {{ __('client.services.renewals.index') }}
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('client.services.renewals.manage') }}
                        </p>
                    </div>
                </div>
                <div class="border rounded-lg overflow-hidden dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                        <tr>

                            <th scope="col" class="px-6 py-3 text-start">
                                <div class="flex items-center gap-x-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-gray-200">
                      {{ __('client.services.renewals.period') }}
                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-start">
                                <div class="flex items-center gap-x-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-gray-200">
                      {{ __('global.invoice') }}
                    </span>
                                </div>
                            </th>

                            <th scope="col" class="px-6 py-3 text-start">
                                <div class="flex items-center gap-x-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-gray-200">
                      {{ __('store.price') }}
                    </span>
                                </div>
                            </th>

                            <th scope="col" class="px-6 py-3 text-start">
                                <div class="flex items-center gap-x-2">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-gray-200">
                                      {{ __('client.services.renewals.date') }}
                                    </span>
                                </div>
                            </th>


                            <th scope="col" class="px-6 py-3 text-end"></th>
                        </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @if ($renewals->isEmpty())

                            <tr class="bg-white hover:bg-gray-50 dark:bg-slate-900 dark:hover:bg-slate-800">
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex flex-auto flex-col justify-center items-center p-2 md:p-3">
                                        <p class="text-sm text-gray-800 dark:text-gray-400">
                                            {{ __('global.no_results') }}
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                        @foreach($renewals as $renewal)
                            @if ($renewal->invoice == null)
                                @continue
                            @endif
                            <tr class="bg-white hover:bg-gray-50 dark:bg-slate-900 dark:hover:bg-slate-800">

                                <td class="h-px w-px whitespace-nowrap">
                    <span class="block px-6 py-2">
                      <span class="text-sm text-gray-600 dark:text-gray-400">#{{ $renewal->period }}</span>
                    </span>
                                </td>
                                <td class="h-px w-px whitespace-nowrap">
                    <a href="{{ route('front.invoices.show', ['invoice' => $renewal->invoice]) }}" class="block px-6 py-2">
                      <span class="font-mono text-sm text-blue-600 dark:text-blue-500">{{ $renewal->invoice->identifier() }}</span>
                    </a>
                                </td>
                                <td class="h-px w-px whitespace-nowrap">
                    <span class="block px-6 py-2">
                      <span class="text-sm text-gray-600 dark:text-gray-400">{{ formatted_price($renewal->invoice->subtotal, $renewal->invoice->currency) }}</span>
                    </span>
                                </td>
                                <td class="h-px w-px whitespace-nowrap">
                    <span class="block px-6 py-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $renewal->start_date->format('d/m/y') }} - {{ $renewal->end_date ? $renewal->end_date->format('d/m/y') : 'Undefined' }}</span>
                    </span>
                                </td>
                                <td class="h-px w-px whitespace-nowrap">
                                    <a href="{{ route('front.invoices.show', ['invoice' => $renewal->invoice]) }}" class="block">
                                        <span class="px-6 py-1.5">
                                          <span class="py-1 px-2 inline-flex justify-center items-center gap-2 rounded-lg border font-medium bg-white text-gray-700 shadow-sm align-middle hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white focus:ring-blue-600 transition-all text-sm dark:bg-slate-900 dark:hover:bg-slate-800 dark:border-gray-700 dark:text-gray-400 dark:hover:text-white dark:focus:ring-offset-gray-800">
                                              <i class="bi bi-eye-fill"></i>
                                            {{ __('global.view') }}
                                          </span>
                                        </span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @if (!empty($service->pricingAvailable(true)))
                <form method="POST" action="{{ route('front.services.billing', ['service' => $service]) }}">
                    @csrf

                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mt-2 mb-2">
                        {{ __('client.services.managerenew') }}
                    </h2>
                    <?php $billing = $service->billing; $product = $service->product ?>


                    <div class="grid sm:grid-cols-3 gap-2" id="basket-billing-section">
                    @foreach ($service->pricingAvailable(true) as $pricing)
                            <label for="billing-{{ $pricing->recurring }}" class="flex p-3 block w-full bg-white border border-gray-200 rounded-lg text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400">
                                <span class="dark:text-gray-400 font-semibold">@if ($pricing->isFree()){{ __('global.free') }} @else {{ $pricing->recurringPayment() }} {{ $pricing->getSymbol() }} @endif {{ $pricing->recurring()['translate'] }}<p class="text-gray-500">{{ $pricing->pricingMessage() }} @if ($pricing->hasDiscountOnRecurring($product->getFirstPrice()))<span class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-teal-100 text-teal-800 dark:bg-teal-800/30 dark:text-teal-500">-{{ $pricing->getDiscountOnRecurring($product->getFirstPrice()) }}%</span>@endif</p></span>
                                <input type="radio" name="billing" value="{{ $pricing->recurring }}" {{ $billing == $pricing->recurring ? 'checked' : '' }} data-pricing="{{ $pricing->toJson()  }}" class="shrink-0 ms-auto mt-0.5 border-gray-200 rounded-full text-indigo-600 focus:ring-indigo-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-gray-800 dark:border-gray-700 dark:checked:bg-indigo-600 dark:checked:border-indigo-600 dark:focus:ring-offset-gray-800" id="billing-{{ $pricing->recurring }}">
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-2">
                            @foreach ($gateways as $gateway)
                            <div class="flex p-1">
                                <input type="radio" {{ $loop->first ? 'checked'  : ''}} name="gateway" value="{{ $gateway->uuid  }}" id="gateway-{{ $gateway->uuid }}" class="shrink-0 mt-0.5 border-gray-200 rounded-full text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800" id="hs-default-radio">
                                <label for="gateway-{{ $gateway->uuid }}" class="text-sm text-gray-500 ms-2 dark:text-neutral-400">{{ $gateway->name }}</label>
                            </div>
                            @endforeach
                    </div>
                    <button class="btn btn-primary mt-2">{{ __('global.save') }}</button>
                    <button class="btn btn-secondary mt-2" name="pay">{{ __('client.services.renewals.saveandrenew') }}</button>
                </form>
                    @endif
            </div>
        </div>
        <div class="md:w-1/4">
            <div class="grid grid-col-1">

                <a href="{{ route('front.services.show', ['service' => $service]) }}" class="hs-dropdown-toggle btn-action-with-icon mb-2 p-3">
                    <svg class="flex-shrink-0 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
                    {{ __('client.services.managebtn') }}
                </a>
                @if (auth('admin')->check())

                    <a href="{{ route('admin.services.show', ['service' => $service]) }}" class="hs-dropdown-toggle btn-action-with-icon mb-2 p-3 text-primary">
                        <i class="bi bi-box text-lg"></i>
                        {{ __('client.services.manageserviceonadmin') }}
                    </a>
                    @endif
                <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-slate-900 dark:border-gray-800">
                    <div class="p-4 md:p-5 flex gap-x-4">
                        <div class="flex-shrink-0 flex justify-center items-center w-[46px] h-[46px] bg-indigo-100 rounded-lg dark:bg-gray-800">
                            <svg class="flex-shrink-0 w-5 h-5 text-gray-600 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line></svg>
                        </div>
                        <div class="grow">
                            <div class="flex items-center gap-x-2">
                                <p class="text-xs uppercase tracking-wide text-gray-500">
                                    {{ $service->name }}
                                </p>
                            </div>
                            <div class="mt-1 flex items-center gap-x-2">
                                <x-badge-state state="{{ $service->status }}"></x-badge-state>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($service->server_id != null)
                <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-slate-900 dark:border-gray-800 mt-2">
                    <div class="p-4 md:p-5 flex gap-x-4">
                        <div class="flex-shrink-0 flex justify-center items-center w-[46px] h-[46px] bg-indigo-100 rounded-lg dark:bg-gray-800">
                            <svg class="flex-shrink-0 w-5 h-5 text-gray-600 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>

                        <div class="grow">
                            <div class="flex items-center gap-x-2">
                                <p class="text-xs uppercase tracking-wide text-gray-500">
                                    {{ __('client.services.server') }}
                                </p>
                            </div>
                            <div class="mt-1 flex items-center gap-x-2">
                                <h3 class="text-xl font-medium text-gray-800 dark:text-gray-200">
                                    {{ $service->server->name }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @if ($service->expires_at != null)

                    <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-slate-900 dark:border-gray-800 mt-2">
                        <div class="p-4 md:p-5 flex gap-x-4">
                            <div class="flex-shrink-0 flex justify-center items-center w-[46px] h-[46px] bg-indigo-100 rounded-lg dark:bg-gray-800">
                                <svg class="flex-shrink-0 w-5 h-5 text-gray-600 dark:text-gray-400"xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/></svg>
                            </div>

                            <div class="grow">
                                <div class="flex items-center gap-x-2">
                                    <p class="text-xs uppercase tracking-wide text-gray-500">
                                        {{ __('client.services.expire_date') }}
                                    </p>
                                </div>
                                <div class="mt-1 flex items-center gap-x-2">
                                    <h3 class="text-xl font-medium text-gray-800 dark:text-gray-200">
                                        <x-service-days-remaining expires_at="{{ $service->expires_at }}" state="{{ $service->status }}" date_at="{{ $service->status == 'expired' ? $service->expires_at->format('d/m/y') : ($service->suspended_at ? $service->suspended_at->format('d/m/y') : '') }}"></x-service-days-remaining>
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-slate-900 dark:border-gray-800 mt-2">
                    <div class="p-4 md:p-5 flex gap-x-4">
                        <div class="flex-shrink-0 flex justify-center items-center w-[46px] h-[46px] bg-indigo-100 rounded-lg dark:bg-gray-800">
                            <svg class="flex-shrink-0 w-5 h-5 text-gray-600 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                        </div>

                        <div class="grow">
                            <div class="flex items-center gap-x-2">
                                <p class="text-xs uppercase tracking-wide text-gray-500">
                                    {{ __('store.price') }}
                                </p>
                            </div>
                            <div class="mt-1 flex items-center gap-x-2">
                                <h3 class="text-xl font-medium text-gray-800 dark:text-gray-200">
                                    {{ formatted_price($service->price, currency()) }}
                                    <span class="text-gray-500 text-sm">/{{ $service->recurring()['unit'] }}</span>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-slate-900 dark:border-gray-800 mt-2">
                    <div class="p-4 md:p-5 flex gap-x-4">
                        <div class="flex-shrink-0 flex justify-center items-center w-[46px] h-[46px] bg-indigo-100 rounded-lg dark:bg-gray-800">
                            <svg class="flex-shrink-0 w-5 h-5 text-gray-600 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>

                        <div class="grow">
                            <div class="flex items-center gap-x-2">
                                <p class="text-xs uppercase tracking-wide text-gray-500">
                                    {{ __('client.services.subusers.index') }}
                                </p>
                            </div>
                            <div class="mt-1 flex items-center gap-x-2">
                                <a href="#" class="btn-action-with-icon">{{ __('client.services.subusers.manage') }}</a>
                            </div>
                        </div>
                    </div>
                </div>-->
            </div>
        </div>
        </div>
    </div>
@endsection
