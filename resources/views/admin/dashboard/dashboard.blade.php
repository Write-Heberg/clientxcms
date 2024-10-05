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
@section('content')
@if ($in_debug && staff_has_permission(\App\Models\Core\Permission::MANAGE_SETTINGS))
    <div class="alert text-yellow-800 bg-yellow-100 mt-2 mb-2" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        {{ __('admin.dashboard.in_debug') }}
    </div>
@endif
@if ($frozen)
    <div class="alert text-yellow-800 bg-yellow-100 mt-2 mb-2" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        {{ __('admin.dashboard.frozen') }}
    </div>
@endif

@if ($notification_error)
    <div class="alert text-yellow-800 bg-yellow-100 mt-2 mb-2" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        {{ __('admin.dashboard.notification_error', ['message' => $notification_error]) }}
    </div>
@endif
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                    @foreach ($widgets as $widget)

                    <div class="flex flex-col shadow-sm rounded-xl dark:bg-gray-800 bg-gray-100">
                        <div class="p-4 md:p-5 flex gap-x-4">
                            <div class="flex-shrink-0 flex justify-center items-center w-[46px] h-[46px] bg-gray-100 rounded-lg  dark:bg-slate-900 dark:border-gray-800">
                                <i class="{{ $widget->icon }} text-black dark:text-white"></i>
                            </div>

                            <div class="grow">
                                <div class="flex items-center gap-x-2">
                                    <p class="text-xs uppercase tracking-wide text-gray-500">
                                        {{ __($widget->title) }}
                                    </p>

                                    @if ($widget->tooltip)
                                        <div class="hs-tooltip">
                                            <div class="hs-tooltip-toggle">
                                                <svg class="flex-shrink-0 w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                                                <span class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded shadow-sm dark:bg-slate-700" role="tooltip">
                {{ ($widget->tooltip->tooltip) }}
              </span>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @if ($widget->tooltip)
                                    <span class="inline-flex items-center gap-x-1 py-0.5 px-2 text-sm rounded-full text-{{ $widget->tooltip->color }}-600 dark:text-{{ $widget->tooltip->color }}-100">
                                                          <i class="{{ $widget->tooltip->icon }} text-{{ $widget->tooltip->color }}-600"></i>

                                        <span class="inline-block text-xs font-medium">
                                            {{ $widget->tooltip->value }}
                                          </span>
                                        </span>
                                @endif
                            </div>
                                <div class="mt-1 flex items-center gap-x-2">
                                    <h3 class="{{ $widget->uuid == 'cron' ? 'text-sm sm:text-2sm' : 'text-xl sm:text-2xl' }} font-medium text-gray-800 dark:text-gray-200">
                                        {{ $widget->value }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
        <div class="grid grid-cols-4 gap-4 mt-8">
            @foreach($cards as $card)
                <div class="card-sm col-span-4 {{ $card->cols != 1 ? 'sm:col-span-' . $card->cols : 'sm:col-span-1' }}">
                    {!! $card->render() !!}
                </div>
                @endforeach
        </div>
@endsection
