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
@section('content')

    <div class="max-w-[85rem] py-5 lg:py-7 mx-auto">
        <div class="sm:w-11/12 lg:w-3/4 mx-auto">
            <div class="flex flex-col md:flex-row gap-4">

            <div class="md:w-2/3">
        @include('admin/shared/alerts')
                <form method="POST" class="card" action="{{ route($routePath . '.store') }}">
                    <div class="card-heading">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                                {{ __($translatePrefix . '.create.title') }}
                            </h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __($translatePrefix. '.create.description') }}
                            </p>
                        </div>

                        <div class="mt-4 flex items-center space-x-4 sm:mt-0">
                            <button class="btn btn-primary">
                                {{ __('admin.create') }}
                            </button>
                        </div>
                    </div>
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex flex-col col-span-2">
                            @include('admin/shared/input', ['name' => 'ip', 'label' => __($translatePrefix .  '.ip'), 'value' => $item->ip, 'help' => __($translatePrefix .  '.dhcp_help')])
                        </div>

                        <div class="flex flex-col">
                            @include('admin/shared/input', ['name' => 'netmask', 'label' => __($translatePrefix .  '.netmask'), 'value' => $item->netmask])
                        </div>

                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        <div class="flex flex-col">
                            @include('admin/shared/select', ['name' => 'bridge', 'label' => __($translatePrefix .  '.bridge'), 'value' => $item->bridge, 'options' => $bridges])
                        </div>
                        <div class="flex flex-col">
                            @include('admin/shared/input', ['name' => 'gateway', 'label' => __($translatePrefix .  '.gateway'), 'value' => $item->gateway])
                        </div>

                    <div class="flex flex-col">
                            @include('admin/shared/input', ['name' => 'mtu', 'label' => __($translatePrefix .  '.mtu'), 'value' => $item->mtu, 'type' => 'number'])
                        </div>

                        <div class="flex flex-col">
                            @include('admin/shared/input', ['name' => 'mac', 'label' => __($translatePrefix .  '.mac'), 'value' => $item->mac])
                        </div>

                        <div class="flex flex-col">
                            @include('admin/shared/input', ['name' => 'ipv6', 'label' => __($translatePrefix .  '.ipv6'), 'value' => $item->ipv6])
                        </div>

                        <div class="flex flex-col">
                            @include('admin/shared/input', ['name' => 'ipv6_gateway', 'label' => __($translatePrefix .  '.ipv6_gateway'), 'value' => $item->ipv6_gateway])
                        </div>
                        <div class="flex flex-col">
                            @include('admin/shared/textarea', ['name' => 'notes', 'label' => __($translatePrefix .  '.notes'), 'value' => $item->notes])
                        </div>
                        <div class="flex flex-col">
                            <label for="{{ $name ?? 'status' }}" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-400 mt-2">{{ __('global.status') }}</label>
                            <div class="relative mt-2">
                                <select data-hs-select='{
      "toggleTag": "<button type=\"button\"><span class=\"me-2\" data-icon></span><span class=\"text-gray-800 dark:text-gray-200\" data-title></span></button>",
      "toggleClasses": "hs-select-disabled:pointer-events-none hs-select-disabled:opacity-50 relative py-3 px-4 pe-9 flex items-center text-nowrap w-full cursor-pointer bg-white border border-gray-200 rounded-lg text-start text-sm focus:border-blue-500 focus:ring-blue-500 before:absolute before:inset-0 before:z-[1] dark:bg-gray-700 dark:border-gray-700 dark:text-gray-400 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600",
      "dropdownClasses": "mt-2 z-50 w-full max-h-[300px] p-1 space-y-0.5 bg-white border border-gray-200 rounded-lg overflow-hidden overflow-y-auto dark:bg-slate-900 dark:border-gray-700",
      "optionClasses": "py-2 px-4 w-full text-sm text-gray-800 cursor-pointer hover:bg-gray-100 rounded-lg focus:outline-none focus:bg-gray-100 dark:bg-slate-900 dark:hover:bg-slate-800 dark:text-gray-400 dark:focus:bg-slate-800",
      "optionTemplate": "<div><div class=\"flex items-center\"><div class=\"me-2\" data-icon></div><div class=\"font-semibold text-gray-800 dark:text-gray-200\" data-title></div></div><div class=\"mt-1.5 text-sm text-gray-500\" data-description></div></div>"
    }' class="hidden" name="{{ $name ?? 'status' }}">
                                    <option value="">Choose</option>
                                    <option value="available" {{ $item->status == 'available' ? 'selected' : '' }} data-hs-select-option='{
        "description": "{{ __($translatePrefix . '.states.available.description') }}",
        "icon": "<svg class=\"flex-shrink-0 w-4 h-4 text-gray-800 dark:text-gray-200\" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" class=\"lucide lucide-globe-2\"><path d=\"M21.54 15H17a2 2 0 0 0-2 2v4.54\"/><path d=\"M7 3.34V5a3 3 0 0 0 3 3v0a2 2 0 0 1 2 2v0c0 1.1.9 2 2 2v0a2 2 0 0 0 2-2v0c0-1.1.9-2 2-2h3.17\"/><path d=\"M11 21.95V18a2 2 0 0 0-2-2v0a2 2 0 0 1-2-2v-1a2 2 0 0 0-2-2H2.05\"/><circle cx=\"12\" cy=\"12\" r=\"10\"/></svg>"
      }'>{{ __($translatePrefix . '.states.available.title') }}</option>

                                    <option value="unavailable"  {{ $item->status == 'unreferenced' ? 'selected' : '' }} data-hs-select-option='{
        "description": "{{ __($translatePrefix . '.states.unavailable.description') }}",
        "icon": "<svg class=\"flex-shrink-0 w-4 h-4 text-gray-800 dark:text-gray-200\" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" class=\"lucide lucide-lock\"><path d=\"M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71\"></path><path d=\"M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71\"></path></svg>"
      }'>{{ __($translatePrefix . '.states.unavailable.title') }}</option>
                                    <option value="used" {{ $item->status == 'used' ? 'selected' : '' }} data-hs-select-option='{
        "description": "{{ __($translatePrefix . '.states.used.description') }}",
        "icon": "<svg class=\"flex-shrink-0 w-4 h-4 text-gray-800 dark:text-gray-200\" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" class=\"lucide lucide-lock\"><rect width=\"18\" height=\"11\" x=\"3\" y=\"11\" rx=\"2\" ry=\"2\"/><path d=\"M7 11V7a5 5 0 0 1 10 0v4\"/></svg>"
      }'>{{ __($translatePrefix . '.states.used.title') }}</option>

                                </select>

                                <div class="absolute top-1/2 end-3 -translate-y-1/2">
                                    <svg class="flex-shrink-0 w-3.5 h-3.5 text-gray-500 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                                </div>
                            </div>

                            <div class="mt-4">
                                @include('admin/shared/checkbox', ['name' => 'is_primary', 'label' => __($translatePrefix .  '.is_primary'), 'value' => $item->is_primary])

                            </div>
                        </div>

                        <div class="flex flex-col">
                            @include('admin/shared/select', ['name' => 'server', 'label' => __('provisioning.server'), 'value' => old('server'), 'options' => $servers])
                        </div>

                    </div>
                </form>
            </div>
            <div class="md:w-1/3">
                <div class="card">
                    <div class="card-heading">
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                            {{ __('proxmox::messages.ipam.create.ranges.title') }}
                        </h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route($routePath . '.ranges') }}">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                                <div class="flex flex-col">
                                    @include('admin/shared/input', ['name' => 'block', 'label' => __('proxmox::messages.ipam.create.ranges.block'), 'value' => old('block'), 'help' => __('proxmox::messages.ipam.create.ranges.block_help')])
                                </div>

                                <div class="flex flex-col">
                                    @include('admin/shared/input', ['name' => 'range_mask', 'label' => __($translatePrefix .  '.netmask'), 'value' => old('netmask'), 'type' => 'number'])
                                </div>

                                <div class="flex flex-col">
                                    @include('admin/shared/select', ['name' => 'range_server', 'label' => __('provisioning.server'), 'value' => old('server'), 'options' => $servers])
                                </div>
                                <div class="flex flex-col">
                                    @include('admin/shared/select', ['name' => 'range_bridge', 'label' => __($translatePrefix .  '.bridge'), 'value' => old('rage_bridge'), 'options' => $bridges])
                                </div>
                                <div class="flex flex-col">
                                    @include('admin/shared/input', ['name' => 'range_gateway', 'label' => __($translatePrefix .  '.gateway'), 'value' => old('range_gateway')])
                                </div>

                                <div class="flex flex-col">
                                    @include('admin/shared/input', ['name' => 'range_mtu', 'label' => __($translatePrefix .  '.mtu'), 'value' => old('range_mtu'), 'type' => 'number'])
                                </div>

                                <div class="flex flex-col">
                                    @include('admin/shared/input', ['name' => 'range', 'label' => __($translatePrefix .  '.create.ranges.range'), 'value' => old('range', '1-254')])
                                </div>
                            </div>
                            <div class="mt-4">
                                <button class="btn btn-primary">
                                    {{ __($translatePrefix. '.create.ranges.import') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
