<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin.settings.sidebar')
@section('title', __('admin.settings.core.services.title'))
@section('setting')
    <div class="card">
        <h4 class="font-semibold uppercase text-gray-600 dark:text-gray-400">
            {{ __('admin.settings.core.services.title') }}
        </h4>
        <p class="mb-2 font-semibold text-gray-600 dark:text-gray-400">
            {{ __('admin.settings.core.services.description') }}
        </p>

        <form action="{{ route('admin.settings.core.services') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    @include('shared/input', ['label' => __('admin.settings.core.services.fields.days_before_creation_renewal_invoice'), 'name' => 'core_services_days_before_creation_renewal_invoice', 'value' => setting('core_services_days_before_creation_renewal_invoice', 7), 'type' => 'number', 'min' => 0, 'max' => 365, 'step' => 1])
                </div>
                <div>
                    @include('shared/input', ['label' => __('admin.settings.core.services.fields.days_before_expiration'), 'name' => 'core_services_days_before_expiration', 'value' => setting('core_services_days_before_expiration', 7), 'type' => 'number', 'min' => 0, 'max' => 365, 'step' => 1])
                </div>
                <div>
                    @include('shared/input', ['label' => __('admin.settings.core.services.fields.notify_expiration_days'), 'name' => 'core_services_notify_expiration_days', 'value' => setting('core_services_notify_expiration_days'), 'help' => __('global.separebycomma')])
                </div>
                <div>
                </div>
                <div class="col-span-3">
                    <div class="flex justify-between">
                    <h3 class="font-semibold uppercase text-gray-600 dark:text-gray-400">{{ __('admin.settings.core.services.webhookonrenew') }}</h3>
                    <div class="hs-tooltip [--trigger:click]">
                        <div class="hs-tooltip-toggle block text-center">
                            <button type="button" class="inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent text-blue-600 hover:text-blue-800 disabled:opacity-50 disabled:pointer-events-none dark:text-blue-500 dark:hover:text-blue-400">
                                {{ __('global.preview') }}
                                <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m18 15-6-6-6 6"></path>
                                </svg>
                            </button>

                            <div class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible hidden opacity-0 transition-opacity absolute invisible z-10 max-w-xs w-full bg-white border border-gray-100 text-start rounded-xl shadow-md dark:bg-neutral-800 dark:border-neutral-700" role="tooltip">
                                <div class="p-4">
                                    <div class="mb-3 flex justify-between items-center gap-x-3">
                                    <img src="https://cdn.clientxcms.com/ressources/docs/service.png">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                    <!-- End Popover -->
                    @include('shared/password', ['label' => __('webhook.url'), 'name' => 'core_services_webhook_url', 'value' => setting('core_services_webhook_url')])
                </div>
                    @method('PUT')
            </div>
            <button type="submit" class="btn btn-primary mt-4">{{ __('global.save') }}</button>
        </form>
@endsection
