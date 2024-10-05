<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@section('title', 'Dashboard')
@extends('admin.layouts.admin')
@section('scripts')
    <script src="{{ Vite::asset('resources/global/js/admin/customcanvas.js')  }}" type="module"></script>
@endsection
@section('content')
    <nav class="relative z-0 flex border rounded-xl overflow-hidden dark:border-slate-700" aria-label="Tabs" role="tablist">
        @foreach ($widgets as $name => $items)

        <button type="button" class="hs-tab-active:border-b-blue-600 hs-tab-active:text-gray-900 dark:hs-tab-active:text-white relative dark:hs-tab-active:border-b-blue-600 min-w-0 flex-1 bg-white first:border-s-0 border-s border-b-2 py-4 px-4 text-gray-500 hover:text-gray-700 text-sm font-medium text-center overflow-hidden hover:bg-gray-50 focus:z-10 focus:outline-none focus:text-blue-600 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-800 dark:border-l-slate-700 dark:border-b-slate-700 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-400 {{ $loop->first ? 'active' : '' }}" id="earn-title-{{ Str::slug($name) }}" data-hs-tab="#earn-tab-{{ Str::slug($name) }}" aria-controls="earn-tab-{{ Str::slug($name) }}" role="tab">
            {{ $name }}
        </button>
        @endforeach
    </nav>

    <div class="mt-3">
        @foreach ($widgets as $name => $items)
        <div id="earn-tab-{{ Str::slug($name) }}" class="{{ $loop->first ? '' : 'hidden' }}" role="tabpanel" aria-labelledby="earn-title-{{ Str::slug($name) }}">
            <div class="grid md:grid-cols-6 border border-gray-200 shadow-sm rounded-xl overflow-hidden dark:border-slate-800">
                @foreach ($items as $widget)
                    <a class="block p-4 md:p-5 relative bg-white hover:bg-gray-50 before:absolute before:top-0 before:start-0 before:w-full before:h-px md:before:w-px md:before:h-full before:bg-gray-200 before:first:bg-transparent dark:bg-gray-800 dark:hover:bg-gray-700 dark:before:bg-gray-700" href="#">
                        <div class="flex md:grid lg:flex gap-y-3 gap-x-5">
                            <i class="{{ $widget->icon }}"></i>
                            <div class="grow">
                                <p class="text-xs uppercase tracking-wide font-medium text-gray-800 dark:text-slate-200">
                                    {{ $widget->title }}
                                </p>
                                <h3 class="mt-1 text-xl sm:text-2xl font-semibold text-blue-600 dark:text-blue-500">
                                    {{ $widget->value }}
                                </h3>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mt-8">
        <div class="card-sm col-span-2">
            @include('admin.dashboard.cards.best-selling', ['dto' => $bestSelling['dto'], 'week' => $bestSelling['week'], 'month' => $bestSelling['month']])
            <h3 class="text-xs font-semibold uppercase text-gray-600 dark:text-gray-400 mb-2 mt-2">{{ __('admin.dashboard.widgets.best_selling.title2') }}</h3>

            <div class="grid grid-cols-4 gap-2">

                <div class="col-span-2">
                    <ul class="mt-3 flex flex-col">
                        @for ($i = 0; $i < $bestSelling['split']; $i++)
                        <li class="inline-flex items-center gap-x-2 py-3 px-4 text-sm border text-gray-800 -mt-px first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:border-neutral-700 dark:text-neutral-200">
                            <div class="flex items-center justify-between w-full">
                                <span>{{ $bestSelling['productsNames'][$bestSelling['products'][$i]->related_id] ?? 'Not found' }}</span>
                                <span>{{ formatted_price($bestSelling['products'][$i]->price) }}</span>
                            </div>
                        </li>
                        @endfor
                    </ul>
                </div>
                <div class="col-span-2">
                    <ul class="mt-3 flex flex-col">
                        @for ($i = $bestSelling['split']; $i < count($bestSelling['products']); $i++)
                            <li class="inline-flex items-center gap-x-2 py-3 px-4 text-sm border text-gray-800 -mt-px first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:border-neutral-700 dark:text-neutral-200">
                                <div class="flex items-center justify-between w-full">
                                    <span>{{ $bestSelling['productsNames'][$bestSelling['products'][$i]->related_id] ?? 'Not found' }}</span>
                                    <span>{{ formatted_price($bestSelling['products'][$i]->price) }}</span>
                                </div>
                            </li>
                    @endfor
                </div>
            </div>
        </div>
    <div class="card-sm col-span-2 row-span-1">
        <div class="grid grid-cols-1 sm:grid-cols-2">
    <div class="flex flex-col">
            <div class="card-heading">
                <h3 class="text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">{{ __('admin.dashboard.earn.gateway_canvas') }}</h3>
            </div>
            <div class="-m-1.5 overflow-x-auto">
                <div class="p-1.5 min-w-full inline-block align-middle">

                    @if ($gateways->isEmpty())
                        <p>{{ __("global.no_results") }}
                    @else
                        <div class="chart-responsive">
                            <canvas height="140" is="custom-canvas" data-labels="{{ $gateways->getLabels() }}" data-backgrounds="{{ $gateways->getColors() }}" data-set="{{ $gateways->getValues() }}" ></canvas>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div>
            <div class="flex flex-col gap-y-3 lg:gap-y-5 p-2 md:p-3 bg-white border shadow-sm rounded-xl dark:bg-slate-900 dark:border-gray-800">
                <div class="inline-flex justify-center items-center">
                    <span class="w-2 h-2 inline-block bg-green-500 rounded-full me-2"></span>
                    <span class="text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">{{ __('admin.dashboard.earn.service_can_renewal') }}</span>
                </div>

                <div class="text-center">
                    <h3 class="text-3xl sm:text-4xl lg:text-5xl font-semibold text-gray-800 dark:text-gray-200">
                        {{ $services['total'] }}
                    </h3>
                </div>

                <dl class="flex justify-center items-center divide-x divide-gray-200 dark:divide-gray-700">
                    <dt class="pe-3">
                        <div class="text-center">
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $services['already_renewed'] }}</span>
                        </div>
                        <span class="block text-sm text-gray-500">{{ __('admin.dashboard.earn.already_renewed') }}</span>

                    </dt>

                    <dd class="text-start ps-3">
                        <div class="text-center">
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $services['expires_soon'] }}</span>
                        </div>
                        <span class="block text-sm text-gray-500">{{ __('admin.dashboard.earn.expire_soon') }}</span>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
        <canvas height="140" is="custom-canvas" data-labels='{!! $months->getLabels() !!}' data-backgrounds='{{ $months->getColors() }}' data-set='{!! $months->getValues() !!}' data-type="line" data-suffix="{{ currency_symbol() }}" title="{{ __('admin.dashboard.earn.graph_month') }}"></canvas>

        <h3 class="text-xs font-semibold uppercase text-gray-600 dark:text-gray-400 mb-2 mt-7">{{ __('admin.dashboard.earn.last_orders') }}</h3>

        <div class="border rounded-lg overflow-hidden dark:border-gray-700">

            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>

                <tr>

                    <th scope="col" class="px-6 py-3 text-start">
                        <div class="flex items-center gap-x-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-gray-200">
                      #
                    </span>
                        </div>
                    </th>

                    <th scope="col" class="px-6 py-3 text-start">
                        <div class="flex items-center gap-x-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-gray-200">
                                            {{ __('store.total') }}
                    </span>
                        </div>
                    </th>

                    <th scope="col" class="px-6 py-3 text-start">
                        <div class="flex items-center gap-x-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-gray-200">
                                            {{ __('client.invoices.itemname') }}
                    </span>
                        </div>
                    </th>

                    <th scope="col" class="px-6 py-3 text-start">
                        <div class="flex items-center gap-x-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-gray-200">
                      {{ __('global.created') }}
                    </span>
                        </div>
                    </th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @if (count($lastorders) == 0)
                    <tr class="bg-white hover:bg-gray-50 dark:bg-slate-900 dark:hover:bg-slate-800">
                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex flex-auto flex-col justify-center items-center p-2 md:p-3">
                                <p class="text-sm text-gray-800 dark:text-gray-400">
                                    {{ __('global.no_results') }}
                                </p>
                            </div>
                        </td>
                    </tr>
                @endif
                @foreach($lastorders as $item)

                    <tr class="bg-white hover:bg-gray-50 dark:bg-slate-900 dark:hover:bg-slate-800">

                        <td class="h-px w-px whitespace-nowrap">
                    <span class="block px-6 py-2">
                      <span class="text-sm text-gray-600 dark:text-gray-400">{{ $item->id }}</span>
                    </span>
                        </td>

                        <td class="h-px w-px whitespace-nowrap">
                    <span class="block px-6 py-2">
                      <span class="text-sm text-gray-600 dark:text-gray-400">{{ formatted_price($item->total, $item->currency) }}</span>
                    </span>
                        </td>
                        <td class="h-px w-px whitespace-nowrap">
                    <span class="block px-6 py-2">
                      <span class="text-sm text-gray-600 dark:text-gray-400">
                          {{ collect($item->items)->pluck('name')->implode(', ') }}
                    </span>
                    </span>
                        </td>

                        <td class="h-px w-px whitespace-nowrap">
                    <span class="block px-6 py-2">
                      <span class="text-sm text-gray-600 dark:text-gray-400">{{ $item->created_at->format('d/m/y H:i') }}</span>
                    </span>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    </div>

@endsection
