<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin/layouts/admin')
@section('title', __($translatePrefix .'.title'))
@section('scripts')
    <script src="{{ Vite::asset('resources/themes/default/js/popupwindow.js') }}" type="module" defer></script>
    <script src="{{ Vite::asset('resources/themes/default/js/filter.js') }}"></script>
@endsection
@section('content')
    <div class="max-w-[85rem] py-5 lg:py-7 mx-auto">
        @include('shared/alerts')
        <div class="flex flex-col">
            <div class="-m-1.5 overflow-x-auto">
                <div class="p-1.5 min-w-full inline-block align-middle">
                    <div class="card">
                        <div class="card-heading">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                                    {{ __($translatePrefix . '.title') }}
                                </h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ __($translatePrefix. '.subheading') }}
                                </p>
                            </div>

                            <div class="flex">
                                <form>
                                    <label for="hs-as-table-product-review-search" class="sr-only">{{ __('global.search') }}</label>
                                    <div class="relative">
                                        <input type="text" value="{{ $search ?? '' }}" id="hs-as-table-product-review-search" name="q" class="py-2 px-3 ps-11 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400 dark:focus:ring-gray-600" placeholder="{{ __('global.search') }}">
                                        <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4">
                                            <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </form>
                                @if (!empty($filters))

                                    <div class="ml-4 hs-dropdown relative inline-block [--placement:bottom-right]" data-hs-dropdown-auto-close="inside">
                                        <button type="button" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-gray-700 dark:text-white dark:hover:bg-gray-800 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600">
                                            <svg class="flex-shrink-0 w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M7 12h10"/><path d="M10 18h4"/></svg>
                                            {{ __('global.filter') }}
                                            @if ($filter)
                                                <span class="ps-2 text-xs font-semibold text-blue-600 border-s border-gray-200 dark:border-gray-700 dark:text-blue-500">
                      {{ count($items) }}
                    </span>
                                            @endif
                                        </button>
                                        <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden mt-2 divide-y divide-gray-200 min-w-[12rem] z-10 bg-white shadow-md rounded-lg mt-2 dark:divide-gray-700 dark:bg-gray-800 dark:border dark:border-gray-700" aria-labelledby="filter-items">
                                            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                                                @foreach ($filters as $current => $label)
                                                    <label for="filter-{{ $current }}" class="flex py-2.5 px-3">
                                                        <input id="filter-{{ $current }}" value="{{ $current }}" type="checkbox" class="filter-checkbox shrink-0 mt-0.5 border-gray-300 rounded text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-gray-600 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800" @if ($current == $filter) checked @endif>
                                                        <span class="ms-3 text-sm text-gray-800 dark:text-gray-200">{{ $label  }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
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
                                            {{ __('client.emails.subject') }}
                    </span>
                                        </div>
                                    </th>

                                    <th scope="col" class="px-6 py-3 text-start">
                                        <div class="flex items-center gap-x-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-gray-200">
                                            {{ __('global.customer') }}
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

                                    <th scope="col" class="px-6 py-3 text-start">
                                                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-gray-200">

                                        {{ __('global.actions') }}
                                                            </span>
                                    </th>
                                </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @if (count($items) == 0)
                                    <tr class="bg-white hover:bg-gray-50 dark:bg-slate-900 dark:hover:bg-slate-800">
                                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="flex flex-auto flex-col justify-center items-center p-2 md:p-3">
                                                <p class="text-sm text-gray-800 dark:text-gray-400">
                                                    {{ __('global.no_results') }}
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                                @foreach($items as $item)

                                    <tr class="bg-white hover:bg-gray-50 dark:bg-slate-900 dark:hover:bg-slate-800">

                                        <td class="h-px w-px whitespace-nowrap">
                    <span class="block px-6 py-2">
                      <span class="text-sm text-gray-600 dark:text-gray-400">{{ $item->id }}</span>
                    </span>
                                        </td>

                                        <td class="h-px w-px whitespace-nowrap">
                    <span class="block px-6 py-2">
                      <span class="text-sm text-gray-600 dark:text-gray-400">{{ $item->subject }}</span>
                    </span>
                                        </td>

                                        <td class="h-px w-px whitespace-nowrap">
                    <span class="block px-6 py-2">
                      <span class="text-sm text-gray-600 dark:text-gray-400">
                          @if ($item->customer != null)
                          <a href="{{ route('admin.customers.show', ['customer' => $item->customer]) }}">
                          {{ $item->customer->fullName }}</span>
                        </a>
                        @else
                            {{ $item->recipient }}
                        @endif
                    </span>
                                        </td>
                                        <td class="h-px w-px whitespace-nowrap">
                    <span class="block px-6 py-2">
                      <span class="text-sm text-gray-600 dark:text-gray-400">{{ $item->created_at->format('d/m/y H:i') }}</span>
                    </span>
                                        </td>
                                        <td class="h-px w-px whitespace-nowrap">

                                            <a href="{{ route($routePath . '.show', ['email' => $item]) }}"   is="popup-window" >
                                        <span class="py-1.5">
                                          <span class="py-1 px-2 inline-flex justify-center items-center gap-2 rounded-lg border font-medium bg-white text-gray-700 shadow-sm align-middle hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white focus:ring-blue-600 transition-all text-sm dark:bg-slate-900 dark:hover:bg-slate-800 dark:border-gray-700 dark:text-gray-400 dark:hover:text-white dark:focus:ring-offset-gray-800">
                                              <i class="bi bi-eye-fill"></i>
                                            {{ __('global.show') }}
                                          </span>
                                        </span>
                                            </a>
                                            <form method="POST" action="{{ route($routePath . '.show', ['email' => $item]) }}" class="inline" onsubmit="return confirmation()">
                                                @method('DELETE')
                                                @csrf
                                                <button>
                                          <span class="py-1 px-2 inline-flex justify-center items-center gap-2 rounded-lg border font-medium bg-red text-red-700 shadow-sm align-middle hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white focus:ring-blue-600 transition-all text-sm dark:bg-red-900 dark:hover:bg-red-800 dark:border-red-700 dark:text-white dark:hover:text-white dark:focus:ring-offset-gray-800">
                                              <i class="bi bi-trash"></i>

                                            {{ __('global.delete') }}
                                          </span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="py-1 px-4 mx-auto">
                            {{ $items->links('shared.layouts.pagination') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
