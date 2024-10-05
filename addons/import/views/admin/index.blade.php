<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin/layouts/admin')
@section('title', __('import::import.title'))
@section('content')
    <div class="max-w-[85rem] py-5 lg:py-7 mx-auto">
        @include('shared/alerts')
        <div class="flex flex-col">
            <div class="-m-1.5 overflow-x-auto">
                <div class="p-1.5 min-w-full inline-block align-middle">
                    <div class="card">
                        <h4 class="font-semibold uppercase text-gray-600 dark:text-gray-400">
                            {{ __('import::import.title') }}
                        </h4>
                        <p class="mb-2 font-semibold text-gray-600 dark:text-gray-400">
                            {{ __('import::import.description') }}</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-slate-900 dark:border-gray-800">
                                <img class="w-full rounded-t-xl p-6" style="height: 200px" src="{{ Vite::asset('resources/global/clientxcms_text.png') }}" alt="{{ __('import::import.v1.title') }}">
                                <div class="p-4 md:p-5">
                                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">
                                        {{ __('import::import.v1.title') }}
                                    </h3>
                                    <p class="mt-1 text-gray-500 dark:text-neutral-400">
                                    <div class="grid grid-cols-2 gap-3 mb-4">

                                @foreach (collect(__('import::import.v1.importables'))->chunk(3) as $row)
                                        <ul class="space-y-3 text-sm">

                                            @foreach($row as $importable)
                                                <li class="flex space-x-3">
    <span class="size-5 flex justify-center items-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-800/30 dark:text-blue-500">
      <svg class="flex-shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
    </span>
                                                    <span class="text-gray-800 dark:text-gray-400">
      {{ $importable }}
    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                        @endforeach
                                    </div>
                                    <a href="{{ route('admin.import.v1') }}" class="btn-primary w-full">{{ __('import::import.v1.fromv1') }}</a>
                                    </p>
                                </div>
                            </div>
                            <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-slate-900 dark:border-gray-800">
                                <img class="w-full rounded-t-xl p-4" style="height: 200px" src="https://www.whmcs.com/images/logo/whmcs-logo-white.png" alt="{{ __('import::import.whmcs.title') }}">
                                <div class="p-4 md:p-5 h-full">
                                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">
                                        {{ __('import::import.whmcs.title') }}
                                    </h3>
                                    <p class="mt-1 text-gray-500 dark:text-neutral-400">
                                    <div class="grid grid-cols-2 gap-3 mb-4">

                                        @foreach (collect(__('import::import.whmcs.importables'))->chunk(3) as $row)
                                            <ul class="space-y-3 text-sm">

                                                @foreach($row as $importable)
                                                    <li class="flex space-x-3">
    <span class="size-5 flex justify-center items-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-800/30 dark:text-blue-500">
      <svg class="flex-shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
    </span>
                                                        <span class="text-gray-800 dark:text-gray-400">
      {{ $importable }}
    </span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endforeach
                                    </div>
                                    <a href="{{ route('admin.import.whmcs') }}" class="btn-primary w-full">{{ __('import::import.whmcs.fromwhmcs') }}</a>
                                    </p>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
