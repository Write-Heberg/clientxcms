<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('layouts/client')
@section('title', __('client.services.show'))
@section('scripts')
    <script src="{{ Vite::asset('resources/themes/default/js/popupwindow.js') }}" type="module" defer></script>
@endsection
@section('content')
    <div class="max-w-[85rem] py-5 lg:py-7 mx-auto">
    <div class="sm:w-11/12 lg:w-3/4 mx-auto">
        <div class="flex flex-col md:flex-row gap-4">
        <div class="md:w-3/4">
            @include('shared/alerts')
            {!! $panel_html !!}
        </div>
        <div class="md:w-1/4">
            <div class="grid grid-col-1">

                @if ($service->canRenew())
                    @if ($service->isFree())
                        <a class="btn-action-with-icon mb-2 p-3" href="{{ route('front.services.renew', ['service' => $service, 'gateway' => 'balance']) }}">
                            <i class="bi bi-credit-card-2-front-fill text-lg"></i>
                            {{ __('client.services.renewbtn') }}
                        </a>
                    @else
                    <a class="hs-dropdown-toggle btn-action-with-icon mb-2 p-3" href="{{ route('front.services.renewal', ['service' => $service]) }}">
                        <i class="bi bi-credit-card-2-front-fill text-lg"></i>
                        {{ __('client.services.managerenew') }}
                    </a>


                        <div class="hs-dropdown">

                            <button class="hs-dropdown-toggle btn-action-with-icon mb-2 p-3 w-full">
                                <svg class="flex-shrink-0 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/></svg>
                                {{ __('client.services.renewbtn') }}
                                <svg class="hs-dropdown-open:rotate-180 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                            </button>

                            <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-[15rem] bg-white shadow-md rounded-lg dark:bg-gray-800 dark:border dark:border-gray-700 dark:divide-gray-700 after:h-4 after:absolute after:-bottom-4 after:start-0 after:w-full before:h-4 before:absolute before:-top-4 before:start-0 before:w-full" aria-labelledby="hs-dropdown-default">
                                @foreach ($gateways as $gateway)
                                    <a class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-indigo-100 focus:outline-none focus:bg-indigo-100 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300 dark:focus:bg-gray-700" href="{{ route('front.services.renew', ['service' => $service, 'gateway' => $gateway->uuid]) }}">
                                        {{ $gateway->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        @endif
                @endif

                    @if (auth('admin')->check())

                        <a href="{{ route('admin.services.show', ['service' => $service]) }}" class="hs-dropdown-toggle btn-action-with-icon mb-2 p-3 text-primary">
                            <i class="bi bi-box text-lg"></i>
                            {{ __('client.services.manageserviceonadmin') }}
                        </a>
                    @endif

                    @if ($service->canCancel())

                        <button type="button" data-hs-overlay="#hs-cancel" class="hs-dropdown-toggle btn-action-with-icon mb-2 p-2 text-danger">
                            <i class="bi bi-x-octagon-fill text-sm"></i>
                            {{ __('client.services.cancel.index') }}
                        </button>

                        <div id="hs-cancel" class="hs-overlay hs-overlay-open:translate-x-0 hidden translate-x-full fixed top-0 end-0 transition-all duration-300 transform h-full max-w-lg w-full z-[80] bg-white border-s dark:bg-gray-800 dark:border-gray-700" tabindex="-1">
                            <div class="flex justify-between items-center py-3 px-4 border-b dark:border-gray-700">
                                <h3 class="font-bold text-gray-800 dark:text-white">
                                    {{ __('client.services.cancel.index') }}
                                </h3>
                                <button type="button" class="flex justify-center items-center size-7 text-sm font-semibold rounded-full border border-transparent text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:text-white dark:hover:bg-gray-700 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600" data-hs-overlay="#hs-cancel">
                                    <span class="sr-only">{{ __('global.closemodal') }}</span>
                                    <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                </button>
                            </div>
                            <div class="p-4">
                                <p class="text-gray-800 dark:text-gray-400">
                                    {{ __('client.services.cancel.subtitle') }}
                                </p>
                                <form action="{{ route('front.services.cancel', ['service' => $service]) }}" method="post" class="w-full">
                                    @csrf

                                    <p class="text-gray-800 dark:text-gray-400">
                                        {{ __('client.services.cancel.index_description') }}
                                    </p>
                                    @include('shared/select', ['name' => 'reason', 'label' => __('client.services.cancel.reason'), 'options' => $cancellation_reasons, 'value' => old('reason')])
                                    @include('shared/textarea', ['name' => 'message', 'label' => __('client.services.cancel.message'), 'value' => old('message')])
                                    @if (!$service->isOnetime())
                                        @include('shared/select', ['name' => 'expiration', 'label' => __('client.services.cancel.expiration'), 'options' => $cancellation_expirations, 'value' => old('expiration')])
                                    @endif
                                    <div class="flex">
                                        <button type="button" data-hs-overlay="#hs-cancel" class="mt-2 mr-3 py-3 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-green-500 shadow-sm hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-gray-700 dark:hover:bg-gray-800 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600">
                                            {{ __('client.services.cancel.back') }}
                                        </button>
                                        <button type="submit" class="mt-2 py-2 px-2 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-red-500 shadow-sm hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-gray-700 dark:hover:bg-gray-800 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600">
                                            {{ __('client.services.cancel.index') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                    @if ($service->canUncancel())

                        <form action="{{ route('front.services.cancel', ['service' => $service]) }}" method="post" class="grid">
                            @csrf

                            <button type="submit"  class="hs-dropdown-toggle btn-action-with-icon mb-2 p-1 btn-primary">
                                <i class="bi bi-check text-lg"></i>
                                {{ __('client.services.cancel.uncancel') }}
                            </button>
                        </form>
                    @endif
                <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-slate-900 dark:border-gray-800">
                    <div class="p-4 pb-0 md:p-5 md:pb-2 flex gap-x-4">
                        <div class="flex-shrink-0 flex justify-center items-center w-[46px] h-[46px] bg-indigo-100 rounded-lg dark:bg-gray-800">
                            <svg class="flex-shrink-0 w-5 h-5 text-gray-600 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line></svg>
                        </div>

                        <div class="grow">
                            <div class="flex items-center gap-x-2">

                                <div class="hs-tooltip inline-block">
                                    <a  data-hs-overlay="#changename-modal" href="#" class="hs-tooltip-toggle w-8 h-8 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-full border border-transparent text-gray-500 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600">
                                        <svg class="flex-shrink-0 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
                                        <span class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded shadow-sm dark:bg-slate-700" role="tooltip">
            {{ __('client.services.changename') }}
          </span>
                                    </a>
                                </div>
                                <p class="text-xs uppercase tracking-wide text-gray-500">
                                    {{ $service->name }}
                                </p>
                            </div>
                            <div class="mt-1 flex items-center gap-x-2">
                                <x-badge-state state="{{ $service->status }}"></x-badge-state>
                            </div>

                        </div>
                    </div>
                    @if ($service->canRenew())

                        @endif
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
                                        <x-service-days-remaining expires_at="{{ $service->expires_at }}" state="{{ $service->status }}" date_at="{{ $service->status == 'expired' ? $service->expires_at->format('d-m-y') : ($service->suspended_at ? $service->suspended_at->format('d-m-y') : '') }}"></x-service-days-remaining>
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
                <div class="flex flex-col bg-white shadow-sm rounded-xl dark:bg-slate-900 dark:border-gray-800 mt-2">
                    <div class="max-w-xs flex flex-col">
                        @foreach ($tabs as $tab)
                            <a {{ !$tab->active ? 'disabled="true"' : '' }} class="{{ $loop->first ? 'provisioning-tab-first ' : ($loop->last ? 'provisioning-tab-last ' : '') }}{{ !$tab->active ? 'provisioning-tab-disabled' : (($current_tab && $current_tab->uuid == $tab->uuid) ? 'provisioning-tab-active' : 'provisioning-tab') }}" href="{{ $tab->active ? $tab->route($service->id) : '' }}" {!! $tab->popup ? 'is="popup-window"' : '' !!} {!! $tab->newwindow ? 'target="_blank"' : '' !!}>
                                <svg class="flex-shrink-0 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $tab->icon !!}</svg>
                                {{ $tab->title }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

        <div id="changename-modal" class="hs-overlay hs-overlay-open:translate-x-0 hidden translate-x-full fixed top-0 end-0 transition-all duration-300 transform h-full max-w-xs w-full z-[80] bg-white border-s dark:bg-neutral-800 dark:border-neutral-700" tabindex="-1">
            <div class="flex justify-between items-center py-3 px-4 border-b dark:border-neutral-700">
                <h3 class="font-bold text-gray-800 dark:text-white">
                    {{ __('client.services.changename') }}
                </h3>
                <button type="button" class="flex justify-center items-center size-7 text-sm font-semibold rounded-full border border-transparent text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:text-white dark:hover:bg-neutral-700" data-hs-overlay="#hs-overlay-right">
                    <span class="sr-only">Close modal</span>
                    <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-4">
                <p class="text-gray-800 dark:text-neutral-400">

                <form method="POST" action="{{ route('front.services.name', ['service' => $service]) }}">
                    @csrf
                    @include('shared/input', ['name' => 'name', 'value' => $service->name, 'placeholder' => __('global.name')])
                    <button type="submit" class="btn-primary w-full mt-2">{{ __('global.save') }}</button>
                </form>
                </p>
            </div>
        </div>
@endsection
