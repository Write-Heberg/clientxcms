<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
<div class="card">
    <div class="card-heading">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                {{ __('client.support.index') }}
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('client.support.index_description') }}
            </p>
        </div>

        <div>
            @if(isset($count) && $count > 3)
                <a class="py-1 px-4 inline-flex gap-x-1 text-sm font-semibold rounded-lg border border-transparent text-blue-600 hover:text-blue-800 disabled:opacity-50 disabled:pointer-events-none dark:text-blue-500 dark:hover:text-blue-400 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600" href="{{ route('front.services.index') }}">
                    {{ __('global.seemore') }}
                    <svg class="flex-shrink-0 w-4 h-4 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            @endif
            @if (!isset($count))
                <a href="{{ route('front.support.create') }}" class="btn-primary text-sm py-2 px-3 gap-x-2 inline-flex items-center">
                    {{ __('client.support.create.newticket') }}
                </a>
                @endif
            <div class="hs-dropdown relative inline-block [--placement:bottom-right]" data-hs-dropdown-auto-close="inside">
                <button id="hs-as-table-table-filter-dropdown" type="button" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-gray-700 dark:text-white dark:hover:bg-gray-800 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600">
                    <svg class="flex-shrink-0 w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M7 12h10"/><path d="M10 18h4"/></svg>
                    {{ __('global.filter') }}
                    @if ($filter)
                        <span class="ps-2 text-xs font-semibold text-blue-600 border-s border-gray-200 dark:border-gray-700 dark:text-blue-500">
                      {{ count($tickets) }}
                    </span>
                    @endif
                </button>
                <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden mt-2 divide-y divide-gray-200 min-w-[12rem] z-10 bg-white shadow-md rounded-lg mt-2 dark:divide-gray-700 dark:bg-gray-800 dark:border dark:border-gray-700" aria-labelledby="filter-items">
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($filters as $current)
                            <label for="filter-service-{{ $current }}" class="flex py-2.5 px-3">
                                <input id="filter-service-{{ $current }}" value="{{ $current }}" type="checkbox" data-redirect="{{ route('front.support.index') }}" class="filter-checkbox shrink-0 mt-0.5 border-gray-300 rounded text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-gray-600 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800" @if ($current == $filter) checked @endif>
                                <span class="ms-3 text-sm text-gray-800 dark:text-gray-200">{{ __('global.states.' . $current) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="border rounded-lg overflow-hidden dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
            <tr>
                <th scope="col" class="px-6 py-3 text-start">
                    <div class="flex items-center gap-x-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-gray-200">
                      {{ __('client.support.subject') }}
                    </span>
                    </div>
                </th>

                <th scope="col" class="px-6 py-3 text-start">
                    <div class="flex items-center gap-x-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-gray-200">
                      {{ __('client.support.priority') }}
                    </span>
                    </div>
                </th>

                <th scope="col" class="px-6 py-3 text-start">
                    <div class="flex items-center gap-x-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-gray-200">
                      {{ __('global.status') }}
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

                <th scope="col" class="px-6 py-3 text-end"></th>
            </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @if (count($tickets) == 0)

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
            @foreach($tickets as $i => $ticket)
                <tr class="bg-white hover:bg-gray-50 dark:bg-slate-900 dark:hover:bg-slate-800">
                    <td class="h-px w-px whitespace-nowrap">

                    <span class="block px-6 py-2">
                      <span class="text-sm text-gray-600 dark:text-gray-400">{{ $ticket->subject }}</span>
                    </span>
                    </td>
                    <td class="h-px w-px whitespace-nowrap">
                    <span class="block px-6 py-2">
                      <span class="text-sm text-gray-600 dark:text-gray-400">{{ $ticket->priorityLabel() }}</span>
                    </span>
                    </td>
                    <td class="h-px w-px whitespace-nowrap">
                        <x-badge-state state="{{ $ticket->status }}"></x-badge-state>
                    </td>
                    <td class="h-px w-px whitespace-nowrap">
                    <span class="block px-6 py-2">
                      <span class="text-sm text-gray-600 dark:text-gray-400">{{ $ticket->created_at->format('d/m/y') }}</span>
                    </span>
                    </td>
                    <td class="h-px w-px whitespace-nowrap">
                        <div class="inline-flex rounded-lg shadow-sm">
                            <a href="{{ route('front.support.show', ['ticket' => $ticket]) }}">
                                <span class="btn-action-with-icon mr-2">
                                   <svg class="flex-shrink-0 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
                                    {{ __('global.show') }}
                                </span>
                            </a>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="py-1 px-4 mx-auto">
        {{ $tickets->links('shared.layouts.pagination') }}
    </div>
</div>
