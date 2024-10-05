<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin/layouts/admin')
@section('title',  __($translatePrefix . '.create.title'))
@section('scripts')
    <script src="{{ Vite::asset('resources/global/js/clipboard.js') }}" type="module"></script>
    <script src="{{ Vite::asset('resources/global/js/flatpickr.js') }}" type="module"></script>
@endsection
@section('content')

    <div class="max-w-[85rem] py-5 lg:py-7 mx-auto">
        <div class="sm:w-11/12 lg:w-3/4 mx-auto">
            @include('admin/shared/alerts')
            <form method="{{ $step == 1 ? 'GET' : 'POST' }}" action="{{ route($step == 1 ? $routePath . '.create' : $routePath .'.store') }}">
                <div class="card">
                    @if ($step == 2)
                        @csrf
                    @endif
                    <div class="card-heading">
                        <div>

                            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                                {{ __($translatePrefix . '.create.title') }}
                            </h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __($translatePrefix. '.create.subheading') }}
                            </p>
                        </div>

                        <div class="mt-4 flex items-center space-x-4 sm:mt-0">
                            @if ($step == 1)
                            <button class="btn btn-primary" name="add">
                                {{ __('admin.services.create.btn'. $step) }}
                            </button>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @if ($step == 1)
                            <div class="flex flex-col">
                                @include('admin/shared/select', ['name' => 'product_id', 'label' => __('global.product'), 'options' => $products, 'value' => old('product_id', $product_id)])
                            </div>

                            <div class="flex flex-col">
                                @include('admin/shared/select', ['name' => 'type', 'label' => __('admin.services.show.type'), 'options' => $types, 'value' => $item->type])
                            </div>

                            <div class="flex flex-col">
                                @include('admin/shared/search-select', ['name' => 'customer_id', 'label' => __('admin.services.customer'), 'options' => $customers, 'value' => 1])
                            </div>
                        @else
                            <div class="flex flex-col">
                                @include('admin/shared/input', ['name' => 'name', 'label' => __('global.name'), 'value' => old('name', $item->name ?? '')])
                            </div>

                            <div class="flex flex-col">
                                @include('admin/shared/flatpickr', ['name' => 'expires_at', 'label' => __('admin.services.show.expires_at'), 'value' => $item->expires_at ? $item->expires_at->format('Y-m-d H:i:s') : null, 'type' => 'date'])
                            </div>

                            <div>
                                <label for="price" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-400 mt-2">{{ __('store.price') }}</label>
                                <div class="relative mt-2">
                                    <input type="number" step="0.01" min="0" id="price" name="price" class="py-3 px-4 ps-9 pe-20 input-text" placeholder="0.00" value="{{ old('price', $item->price) }}">
                                    <div class="absolute inset-y-0 end-0 flex items-center text-gray-500 pe-px">
                                        <label for="currency" class="sr-only">{{ __('global.currency') }}</label>
                                        <select id="currency" name="currency" class="store w-full border-transparent rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:bg-gray-700 dark:text-white dark:border-gray-700 dark:text-gray-400">
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
                                @include('admin/shared/input', ['name' => 'initial_price', 'label' => __('admin.services.show.initial_price'), 'value' => $item->initial_price, 'step' => '0.01', 'min' => '0'])
                            </div>
                            <div class="flex flex-col">
                                @include('admin/shared/select', ['name' => 'server_id', 'label' => __('client.services.server'), 'options' => $servers, 'value' => old('server_id', $item->server_id ?? 'none')])
                            </div>
                            <div class="flex flex-col">
                                @include('admin/shared/select', ['name' => 'billing', 'label' => __('global.recurrences'), 'options' => $recurrings, 'value' => $item->billing])
                            </div>
                            <div class="flex flex-col">
                                @include('admin/shared/textarea', ['name' => 'notes', 'label' => __('admin.services.show.notes'), 'value' => old('notes', $item->notes)])
                            </div>
                            <input type="hidden" name="customer_id" value="{{ $customer_id }}">
                            <input type="hidden" name="product_id" value="{{ $item->product_id ?? 'none' }}">
                            <input type="hidden" name="type" value="{{ $item->type ?? 'none' }}">
                            <input type="hidden" name="status" value="pending">
                            <div class="flex flex-col">
                                @include('admin/shared/input', ['name' => 'max_renewals', 'label' => __('admin.services.show.max_renewals'), 'value' => $item->max_renewals, 'type' => 'number', 'help' => __('admin.blanktonolimit')])
                            </div>
                    </div>
                    @endif
                </div>
                @if ($step == 2)

                <div class="py-3 flex items-center text-sm text-gray-800 before:flex-[1_1_0%] before:border-t before:border-gray-200 before:me-6 after:flex-[1_1_0%] after:border-t after:border-gray-200 after:ms-6 dark:text-white dark:before:border-gray-600 dark:after:border-gray-600">
                    {{ __($translatePrefix . '.create.new') }}</div>
                <div class="card">
                    {!! $dataHTML !!}
                    @if (empty($dataHTML))
                        <div class="alert text-yellow-800 bg-yellow-100 mt-2 mb-2" role="alert">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                            {{ __('admin.services.create.nothingtoshow') }}
                    </div>
                    @endif
                </div>
                        <button class="btn btn-primary" name="create">
                            {{ __('admin.create') }}
                        </button>
                @if (!empty($importHTML))
                    <div class="py-3 flex items-center text-sm text-gray-800 before:flex-[1_1_0%] before:border-t before:border-gray-200 before:me-6 after:flex-[1_1_0%] after:border-t after:border-gray-200 after:ms-6 dark:text-white dark:before:border-gray-600 dark:after:border-gray-600">
                        {!! __($translatePrefix . '.create.import') !!}</div>

                    <div class="card">
                        {!! $importHTML !!}
                    </div>

                        <button class="btn btn-primary" name="import">
                            {{ __('admin.services.create.btn'. $step) }}
                        </button>
                @endif
                    @endif
            </form>
        </div>
    </div>

@endsection
