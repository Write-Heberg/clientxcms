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
    <script src="{{ Vite::asset('resources/global/js/admin/filter.js') }}" type="module"></script>
@endsection
@section('content')
    <div class="max-w-[85rem] py-5 lg:py-7 mx-auto">
        @include('admin/shared/alerts')
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
                                    {{ __($translatePrefix. '.description') }}
                                </p>
                            </div>
                            @include('admin/shared/mass_actions/header', ['searchFields' => $searchFields, 'search' => $search, 'searchField' => $searchField, 'filters' => $filters, 'checkedFilters' => $checkedFilters])

                            <a class="btn btn-primary text-sm ml-2" href="{{ route($routePath . '.create') }}">
                                    {{ __('admin.create') }}
                                </a>
                            </div>
                        </div>
                        <div class="border rounded-lg overflow-hidden dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="mass_action_table">
                                <thead>

                                <th scope="col" class="px-6 py-3 text-start">
                                    <div class="flex items-center gap-x-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-gray-200">
                      <div class="flex items-center h-5">
                    <input id="checkbox-all" type="checkbox" class="border-gray-200 rounded text-blue-600 focus:ring-blue-500 dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800">
                    <label for="checkbox-all" class="sr-only">Checkbox</label>
                  </div>
                    </span>
                                    </div>
                                </th>

                                    <th scope="col" class="px-6 py-3 text-start">
                                        <div class="flex items-center gap-x-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-gray-200">
                      {{ __($translatePrefix . '.ip') }}
                    </span>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-start">
                                        <div class="flex items-center gap-x-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-gray-200">
                      {{ __($translatePrefix . '.gateway') }}
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
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center">
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
                      <span class="text-sm text-gray-600 dark:text-gray-400">
                          <div class="flex items-center h-5">
                    <input id="table-checkbox-{{ $item->id }}" data-id="{{ $item->id }}" data-name="{{ $item->name }}" type="checkbox" class="border-gray-200 rounded text-blue-600 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800">
                    <label for="table-checkbox-{{ $item->id }}" class="sr-only">Checkbox</label>
                              <span class="ml-3">{{ $item->id }}</span>
                  </div>
                      </span>
                    </span>
                                        </td>
                                        <td class="h-px w-px whitespace-nowrap">

                    <span class="block px-6 py-2">
                      <span class="text-sm text-gray-600 dark:text-gray-400">{{ $item->ip }}</span>
                    </span>
                                        </td>
                                        <td class="h-px w-px whitespace-nowrap">
                    <span class="block px-6 py-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $item->gateway }}</span>
                    </span>
                                        </td>

                                        <td class="h-px w-px whitespace-nowrap px-6">
                            @if ($item->status == 'used')
                                                <span class="py-1 px-2 inline-flex items-center gap-x-1 text-xs font-medium bg-red-100 text-red-800 rounded-full dark:bg-red-500/10 dark:text-red-500">
                              <svg class="flex-shrink-0 w-3 h-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                              {{ __($filters[$item->status]) }}
                            </span>
                            @elseif ($item->status == 'unavailable')
                            <span class="py-1 px-2 inline-flex items-center gap-x-1 text-xs bg-gray-100 text-gray-800 rounded-full dark:bg-slate-500/20 dark:text-slate-400">
                              <svg class="flex-shrink-0 w-3 h-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"/><line x1="12" x2="12" y1="2" y2="12"/></svg>
                              {{ __($filters[$item->status]) }}
                            </span>
                            @else
                                                <span class="py-1 px-2 inline-flex items-center gap-x-1 text-xs font-medium bg-teal-100 text-teal-800 rounded-full dark:bg-teal-500/10 dark:text-teal-500">
  <svg class="flex-shrink-0 w-3 h-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/></svg>
                                {{ __($filters[$item->status]) }}
</span>
                            @endif
                                        </td>


                                        <td class="h-px w-px whitespace-nowrap">
                                                        <span class="block px-6 py-2">
                      <span class="text-sm text-gray-600 dark:text-gray-400">{{ $item->created_at->format('d/m/y') }}</span>
                    </span>
                                        </td>
                                        <td class="h-px w-px whitespace-nowrap">

                                            <a href="{{ route($routePath . '.show', ['ipam' => $item]) }}">
                                        <span class="px-1 py-1.5">
                                          <span class="py-1 px-2 inline-flex justify-center items-center gap-2 rounded-lg border font-medium bg-white text-gray-700 shadow-sm align-middle hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white focus:ring-blue-600 transition-all text-sm dark:bg-slate-900 dark:hover:bg-slate-800 dark:border-gray-700 dark:text-gray-400 dark:hover:text-white dark:focus:ring-offset-gray-800">
                                              <i class="bi bi-eye-fill"></i>
                                            {{ __('global.show') }}
                                          </span>
                                        </span>
                                            </a>
                                            <form method="POST" action="{{ route($routePath . '.show', ['ipam' => $item]) }}" class="inline" onsubmit="return confirmation()">
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

                    @include('admin/shared/mass_actions/select')

                </div>
                </div>
            </div>
        </div>
    </div>
    @include('admin/shared/mass_actions/modal')

@endsection
