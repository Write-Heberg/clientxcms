<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin/layouts/admin')
@section('title',  __($translatePrefix . '.show.title', ['name' => $item->name]))
@section('scripts')
    <script src="{{ Vite::asset('resources/global/js/clipboard.js') }}" type="module"></script>
    <script src="{{ Vite::asset('resources/global/js/flatpickr.js') }}" type="module"></script>
    <script src="{{ Vite::asset('resources/global/js/admin/metadata.js') }}" type="module"></script>
    <script src="{{ Vite::asset('resources/themes/default/js/popupwindow.js') }}" type="module"></script>
@endsection
@section('content')

    <div class="flex flex-col md:flex-row gap-4">
                <div class="md:w-2/3">
                    @include('admin/shared/alerts')
                    @if (!isset($intab) || !$intab)
                        <form method="POST" class="card" action="{{ route($routePath . '.update', ['service' => $item]) }}">
                            <div class="card-heading">
                                <div>

                                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                                        {{ __($translatePrefix . '.show.title', ['name' => $item->name]) }}
                                        <x-badge-state state="{{ $item->status }}"></x-badge-state>

                                    </h2>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ __($translatePrefix. '.show.subheading', ['date' => $item->created_at->format('d/m/y'), 'owner' => $item->customer->fullName]) }}
                                    </p>
                                </div>
                                @if (staff_has_permission('admin.manage_services'))

                                <div class="mt-4 flex items-center space-x-4 sm:mt-0">
                                    <button class="btn btn-primary">
                                        {{ __('admin.updatedetails') }}
                                    </button>
                                </div>
                                @endif
                            </div>
                            @method('PUT')
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                            <div class="flex flex-col">
                                @include('/admin/shared/input', ['name' => 'name', 'label' => __('global.name'), 'value' => $item->name])
                            </div>
                                <div class="flex flex-col">
                                    @if ($item->isOnetime())
                                        @include('/admin/shared/input', ['name' => 'expires_at', 'label' => __('admin.services.show.expires_at'), 'value' => __('recurring.onetime'), 'disabled' => true])
                                    @else
                                    @include('/admin/shared/flatpickr', ['name' => 'expires_at', 'label' => __('admin.services.show.expires_at'), 'value' => $item->expires_at ? $item->expires_at->format('Y-m-d H:i:s') : null, 'type' => 'date'])
                                    @endif

                                </div>

                            <div>
                                <label for="price" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-400 mt-2">{{ __('store.price') }}</label>
                                <div class="relative mt-2">
                                    <input type="text" id="price" name="price" class="py-3 px-4 ps-9 pe-20 input-text" placeholder="0.00" value="{{ old('price', $item->price) }}">
                                    <div class="absolute inset-y-0 end-0 flex items-center text-gray-500 pe-px">
                                        <label for="currency" class="sr-only">{{ __('global.currency') }}</label>
                                        <select id="currency" name="currency" class="store w-full border-transparent rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:bg-gray-700 dark:border-gray-700 dark:text-gray-400">
                                            @foreach(currencies() as $currency)
                                                <option value="{{ $currency['code'] }}" @if($currency['code'] == $item->currency) selected @endif>{{ $currency['code'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @error('price')
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

                                <div class="flex flex-col">
                                    @include('/admin/shared/input', ['name' => 'initial_price', 'label' => __('admin.services.show.initial_price'), 'value' => formatted_price($item->initial_price, $item->currency), 'disabled' => true])
                                </div>
                            <div class="flex flex-col">
                                @include('/admin/shared/select', ['name' => 'server_id', 'label' => __('client.services.server'), 'options' => $servers, 'value' => old('server_id', $item->server_id ?? 'none')])
                            </div>
                            <div class="flex flex-col">
                                @include('/admin/shared/select', ['name' => 'billing', 'label' => __('global.recurrences'), 'options' => $recurrings, 'value' => $item->billing])
                            </div>
                            <div class="flex flex-col">
                                @include('/admin/shared/select', ['name' => 'product_id', 'label' => __('global.product'), 'options' => $products, 'value' => old('product_id', $item->product_id ?? 'none')])
                                @include('/admin/shared/textarea', ['name' => 'notes', 'label' => __('admin.services.show.notes'), 'value' => old('notes', $item->notes)])

                            </div>

                                <div class="flex flex-col">
                                    @include('/admin/shared/select', ['name' => 'type', 'label' => __('admin.services.show.type'), 'options' => $types, 'value' => $item->type])
                                        @include('/admin/shared/select', ['name' => 'status', 'label' => __('global.state'), 'options' => $statuses, 'value' => $item->status])
                                </div>

                            <input type="hidden" name="customer_id" value="{{ $item->customer_id }}">
                            <div class="flex flex-col">
                                @include('/admin/shared/input', ['name' => 'max_renewals', 'label' => __('admin.services.show.max_renewals'), 'value' => $item->max_renewals, 'type' => 'number', 'help' => __('admin.blanktonolimit')])
                            </div>

                                </div>
                        </form>
                    @endif

                    {!! $panel_html !!}
                </div>
                <div class="md:w-1/3">
                    <div>
                        @if (staff_has_permission('admin.manage_services'))
                        @if (!$item->isExpired())
                            <form method="POST" action="{{ route('admin.services.action', ['service' => $item, 'action' => 'expire']) }}" onsubmit="return confirmation()">
                                @csrf
                            <button type="submit" class="btn btn-danger w-full mb-2 text-left">
                                <i class="bi bi-trash mr-2"></i>
                                {{ __('admin.services.terminate.btn') }}
                            </button>
                            </form>
                        @endif
                        @if ($item->isActivated())
                            <button type="button" class="btn btn-warning w-full mb-2 text-left" data-hs-overlay="#suspend-overlay">
                                <i class="bi bi-ban mr-2"></i>
                                {{ __('admin.services.suspend.btn') }}
                            </button>
                        @endif
                        @if($item->isSuspended())
                            <button class="btn btn-success mb-2 w-full text-left" data-hs-overlay="#suspend-overlay">
                                <i class="bi bi-check mr-2"></i>
                                {{ __('admin.services.unsuspend.btn') }}
                            </button>
                    @endif
                        @endif
                            @if (staff_has_permission('admin.create_invoices'))

                            <button type="button" class="btn btn-primary w-full mb-2 text-left" data-hs-overlay="#renewals-overlay">
                                <i class="bi bi-arrow-repeat mr-2"></i>

                                {{ __('admin.services.renewals.btn') }}
                            </button>
                            @endif
                            @if (staff_has_permission('admin.show_customers'))

                            <a class="btn btn-info w-full text-left mb-2" href="{{ route('admin.customers.show', ['customer' => $item->customer]) }}">
                            <i class="bi bi-people mr-2"></i>
                            {{ __('admin.services.show.customerbtn') }}
                        </a>
                            @endif
                            @if (staff_has_permission('admin.show_metadata'))

                            <button class="btn btn-secondary mb-2 w-full text-left" id="metadata-button" data-hs-overlay="#metadata-overlay">
                        <i class="bi bi-database mr-2"></i>
                        {{ __('admin.metadata.title') }}
                    </button>
                            @endif
                            @if (staff_has_permission('admin.manage_services'))
                            <button class="btn btn-warning mb-2 w-full text-left" data-hs-overlay="#cancel-overlay">
                                <i class="bi bi-trash2 mr-2"></i>
                                {{ __('admin.services.cancel.btn') }}
                            </button>
                            @endif
                    </div>
                    @if (staff_has_permission('admin.deliver_services'))
                    @if ($item->isPending())
                        <form method="POST" action="{{ route('admin.services.delivery', ['service' => $item]) }}">
                            @csrf
                            <button type="submit" class="btn btn-success w-full mb-2 text-left">
                                <i class="bi bi-truck mr-2"></i>
                                {{ __('admin.services.delivery.btn') }}
                            </button>
                        </form>
                        @else
                            <form method="POST" action="{{ route('admin.services.reinstall', ['service' => $item]) }}">
                                @csrf
                                <button type="submit" class="btn btn-success w-full mb-2 text-left" onclick="return confirmation();">
                                    <i class="bi bi-truck mr-2"></i>
                                    {{ __('admin.services.delivery.btn2') }}
                                </button>
                            </form>
                        @endif
                    @endif

                    @if (count($tabs) != 0)
                        <div class="flex flex-col bg-white shadow-sm rounded-xl dark:bg-slate-900 dark:border-gray-800 mt-2">

                            <div class="w-full flex flex-col">
                                @foreach ($tabs as $tab)
                                    <a {{ !$tab->active ? 'disabled="true"' : '' }} class="{{ $loop->first ? 'provisioning-tab-first ' : ($loop->last ? 'provisioning-tab-last ' : '') }}{{ !$tab->active ? 'provisioning-tab-disabled' : (($current_tab && $current_tab->uuid == $tab->uuid) ? 'provisioning-tab-active' : 'provisioning-tab') }}" href="{{ $tab->active ? $tab->route($item->id, true) : '#' }}" {!! $tab->popup ? 'is="popup-window"' : '' !!}  {!! $tab->newwindow ? 'target="_blank"' : '' !!}>
                                        {!! $tab->icon !!}
                                        {{ $tab->title }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                </div>
    <div id="cancel-overlay" class="hs-overlay hs-overlay-open:translate-x-0 hidden translate-x-full fixed top-0 end-0 transition-all duration-300 transform h-full max-w-xs w-full w-full z-[80] bg-white border-s dark:bg-gray-800 dark:border-gray-700 hidden" tabindex="-1">
        <div class="flex justify-between items-center py-3 px-4 border-b dark:border-gray-700">
            <h3 class="font-bold text-gray-800 dark:text-white">
                    {{ __($translatePrefix . '.cancel.btn') }}
            </h3>
            <button type="button" class="flex justify-center items-center w-7 h-7 text-sm font-semibold rounded-full border border-transparent text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:text-white dark:hover:bg-gray-700 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600" data-hs-overlay="#cancel-overlay">
                <span class="sr-only">{{ __('global.closemodal') }}</span>
                <svg class="flex-shrink-0 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <div class="p-4">

            <form method="POST" action="{{ route('admin.services.action', ['service' => $item, 'action' => 'cancel']) }}">
                <p class="text-gray-800 dark:text-gray-400">

                @csrf
                    @if ($item->cancelled_reason != NULL)
                    @include('/admin/shared/select', ['name' => 'reason', 'label' => __('client.services.cancel.reason'), 'options' => $cancellation_reasons, 'value' => old('reason')])
                    @include('/admin/shared/textarea', ['name' => 'message', 'label' => __($translatePrefix. '.cancel.message'), 'value' => $item->cancelled_reason])
                    @if (!$item->isOnetime())
                        @include('/admin/shared/input', ['name' => 'expiration', 'label' => __('client.services.cancel.expiration'), 'value' => $item->cancelled_at->format('d/m/y H:i')])
                    @endif
                        <button class="btn btn-primary w-full mt-10"> <i class="bi bi-check mr-2"></i>{{ __($translatePrefix . '.cancel.restore') }}</button>

                    @else
                        @include('/admin/shared/select', ['name' => 'reason', 'label' => __('client.services.cancel.reason'), 'options' => $cancellation_reasons, 'value' => old('reason')])
                        @include('/admin/shared/textarea', ['name' => 'message', 'label' => __('client.services.cancel.message'), 'value' => old('message')])
                        @if (!$item->isOnetime())
                            @include('/admin/shared/select', ['name' => 'expiration', 'label' => __('client.services.cancel.expiration'), 'options' => $cancellation_expirations, 'value' => old('expiration')])

                        @endif
                        <button class="btn btn-primary w-full mt-10"> <i class="bi bi-trash2 mr-2"></i>{{ __($translatePrefix . '.cancel.title') }}</button>

                    @endif
                    </p>
            </form>
        </div>
    </div>
            <div id="suspend-overlay" class="hs-overlay hs-overlay-open:translate-x-0 hidden translate-x-full fixed top-0 end-0 transition-all duration-300 transform h-full max-w-xs w-full w-full z-[80] bg-white border-s dark:bg-gray-800 dark:border-gray-700 hidden" tabindex="-1">
                <div class="flex justify-between items-center py-3 px-4 border-b dark:border-gray-700">
                    <h3 class="font-bold text-gray-800 dark:text-white">
                        @if ($item->isActivated())
                            {{ __($translatePrefix . '.suspend.title') }}
                        @else
                            {{ __($translatePrefix . '.unsuspend.title') }}
                            @endif
                    </h3>
                    <button type="button" class="flex justify-center items-center w-7 h-7 text-sm font-semibold rounded-full border border-transparent text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:text-white dark:hover:bg-gray-700 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600" data-hs-overlay="#suspend-overlay">
                        <span class="sr-only">{{ __('global.closemodal') }}</span>
                        <svg class="flex-shrink-0 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <div class="p-4">

                    <form method="POST" action="{{ route('admin.services.action', ['service' => $item, 'action' => $item->isActivated() ? 'suspend' : 'unsuspend']) }}">
                        <p class="text-gray-800 dark:text-gray-400">

                        @csrf
                        @if ($item->isActivated())
                            @include('/admin/shared/textarea', ['name' => 'reason', 'label' => __('admin.services.suspend.reason'), 'value' => old('reason', $item->suspend_reason)])
                            <div class="mt-2">
                            @include('/admin/shared/checkbox', ['name' => 'notify', 'label' => __('admin.services.suspend.notify')])
                        </div>
                        @elseif ($item->suspended_at != null)
                            @include('/admin/shared/textarea', ['name' => 'reason', 'label' => __('admin.services.suspend.reason'), 'value' => $item->suspend_reason, 'disabled' => true])
                            @include('/admin/shared/input', ['name' => 'suspend_at', 'label' => __('admin.services.suspend.suspend_at'), 'disabled' => true,'value' => $item->suspended_at->format('d/m/y H:i')])
                        @endif
                            @if ($item->isActivated())
                            <button class="btn btn-warning w-full mt-10"> <i class="bi bi-ban mr-2"></i>  {{ __($translatePrefix . '.suspend.btn') }}</button>
                        @else
                            <button class="btn btn-success w-full mt-10"> <i class="bi bi-check mr-2"></i>  {{ __($translatePrefix . '.unsuspend.btn') }}</button>
                        @endif
                        </p>
                    </form>
                </div>
            </div>

        <div id="metadata-overlay" class="overflow-x-hidden overflow-y-auto hs-overlay hs-overlay-open:translate-x-0 translate-x-full fixed top-0 end-0 transition-all duration-300 transform h-full max-w-lg w-full w-full z-[80] bg-white border-s dark:bg-gray-800 dark:border-gray-700 hidden" tabindex="-1">
            <div class="flex justify-between items-center py-3 px-4 border-b dark:border-gray-700">
                <h3 class="font-bold text-gray-800 dark:text-white">
                    {{ __($translatePrefix . '.data.title') }}
                </h3>
                <button type="button" class="flex justify-center items-center w-7 h-7 text-sm font-semibold rounded-full border border-transparent text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:text-white dark:hover:bg-gray-700 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600" data-hs-overlay="#metadata-overlay">
                    <span class="sr-only">{{ __('global.closemodal') }}</span>
                    <svg class="flex-shrink-0 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
            <div class="p-4">
                <form method="POST" action="{{ route('admin.services.update_data', ['service' => $item]) }}#metadata">
                    @csrf
                @include('/admin/shared/textarea', ['name' => 'data', 'label' => __('admin.services.data.orderdata'), 'value' => old('data', json_encode($item->data, JSON_PRETTY_PRINT)), 'rows' => 10])
                <button class="btn btn-primary w-full mt-2"> <i class="bi bi-check mr-2"></i>  {{ __('global.save') }}</button>
                </form>
            </div>
            @include('admin/metadata/table', ['item' => $item])
        </div>
        <div id="renewals-overlay" class="hs-overlay hs-overlay-open:translate-x-0 translate-x-full fixed top-0 end-0 transition-all duration-300 transform h-full max-w-lg w-full w-full z-[80] bg-white border-s dark:bg-gray-800 dark:border-gray-700 hidden" tabindex="-1">
            <div class="flex justify-between items-center py-3 px-4 border-b dark:border-gray-700">
                <h3 class="font-bold text-gray-800 dark:text-white">
                        {{ __($translatePrefix . '.renewals.title') }}
                </h3>
                <button type="button" class="flex justify-center items-center w-7 h-7 text-sm font-semibold rounded-full border border-transparent text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:text-white dark:hover:bg-gray-700 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600" data-hs-overlay="#renewals-overlay">
                    <span class="sr-only">{{ __('global.closemodal') }}</span>
                    <svg class="flex-shrink-0 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
            <div class="p-4">
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
                @if (!$item->isOnetime())
                    @if ($item->invoice_id == null)

                    <form method="POST" action="{{ route('admin.services.renew', ['service' => $item]) }}">
                        @csrf
                    <h4 class="font-bold text-gray-600 dark:text-white mt-2 mb-2">
                        {{ __($translatePrefix . '.renewals.create') }}
                    </h4>
                        @foreach ($months->chunk(3) as $row)
                    <ul class="flex flex-col sm:flex-row justify-center w-full">
                        @foreach($row as $recurring => $month)
                        <li class="inline-flex items-center gap-x-2.5 py-3 px-4 text-sm font-medium bg-white border text-gray-800 -mt-px first:rounded-t-lg first:mt-0 last:rounded-b-lg sm:-ms-px sm:mt-0 sm:first:rounded-se-none sm:first:rounded-es-lg sm:last:rounded-es-none sm:last:rounded-se-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                            <div class="relative flex items-start w-full">
                                <div class="flex items-center h-5">
                                    <input id="months-{{ $recurring }}" @if($loop->first) checked="checked" @endif name="billing" value="{{ $recurring }}" type="radio" class="border-gray-200 rounded-full disabled:opacity-50 dark:bg-gray-800 dark:border-gray-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800">
                                </div>
                                <label for="months-{{ $recurring }}" class="ms-3 block w-full text-sm text-gray-600 dark:text-gray-500">
                                    {{ $month['months'] }} {{ __('global.month') }} - {{ formatted_price($prices[$recurring], $item->currency) }}
                                </label>
                            </div>
                        </li>
                            @endforeach
                    </ul>
                        @endforeach

                        <h4 class="font-bold text-gray-600 dark:text-white mt-4 mb-2">
                            {{ __($translatePrefix . '.renewals.fromexistinginvoice') }}
                        </h4>
                        <div class="flex flex-col">
                            @include('/admin/shared/select', ['name' => 'invoice_id', 'label' => __('global.invoice'), 'options' => $invoices, 'value' => old('invoice_id')])
                        </div>

                <div class="mt-2">
                    <button class="btn btn-primary w-full mt-10"> <i class="bi bi-eraser mr-2"></i>  {{ __($translatePrefix . '.renewals.btn2') }}</button>

                </div>
                    </form>
                    @else

                        <div>
                            <div class="flex rounded-lg shadow-sm mt-2">
                                <input type="text" readonly class="input-text" id="invoice_url" value="{{ route('front.invoices.show', ['invoice' => $item->invoice_id]) }}">
                                <button type="button" data-clipboard-target="#invoice_url" data-clipboard-action="copy" data-clipboard-success-text="Copied" class=" js-clipboard w-[2.875rem] h-[2.875rem] flex-shrink-0 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-e-md border border-transparent bg-blue-600 text-white hover:bg-blue-700  dark:focus:ring-1 dark:focus:ring-gray-600">
                                    <svg class="js-clipboard-default w-4 h-4 group-hover:rotate-6 transition" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/></svg>

                                    <svg class="js-clipboard-success hidden w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>

                                </button>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.services.renew', ['service' => $item]) }}">
                            @csrf
                            <div class="mt-2">
                                <button class="btn btn-primary w-full mt-10"> <i class="bi bi-eraser mr-2"></i>  {{ __($translatePrefix . '.renewals.remove') }}</button>
                            </div>
                        </form>
                    @endif
                    @endif
            </div>
        </div>

@endsection
