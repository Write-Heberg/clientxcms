<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin.settings.sidebar')
@section('title', __('maintenance.settings.title'))
@section('setting')
    <div class="card">
        <h4 class="font-semibold uppercase text-gray-600 dark:text-gray-400">
            {{ __('maintenance.settings.title') }}
        </h4>
        <p class="mb-2 font-semibold text-gray-600 dark:text-gray-400">
            {{ __('maintenance.settings.description') }}
        </p>

        <form action="{{ route('admin.settings.core.maintenance') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-4 mt-2">
                <div>
                    @include('shared/checkbox', ['label' => __('maintenance.settings.maintenance_enabled'), 'name' => 'maintenance_enabled', 'checked' => setting('maintenance_enabled')])
                </div>
                <div>
                    @include('shared/textarea', ['label' => __('maintenance.settings.maintenance_message'), 'name' => 'maintenance_message', 'value' => setting('maintenance_message'), 'rows' => 3, 'help' => __('maintenance.settings.maintenance_message_help')])
                </div>
                <div>
                    @include('shared/input', ['label' => __('maintenance.settings.maintenance_url'), 'name' => 'maintenance_url', 'value' => setting('maintenance_url'), 'help' => __('maintenance.settings.maintenance_url_help')])
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    @include('shared/input', ['label' => __('maintenance.settings.maintenance_button_text'), 'name' => 'maintenance_button_text', 'value' => setting('maintenance_button_text')])
                </div>
                <div>
                    @include('shared/input', ['label' => __('maintenance.settings.maintenance_button_url'), 'name' => 'maintenance_button_url', 'value' => setting('maintenance_button_url')])
                </div>
                <div>
                    @include('shared/input', ['label' => __('maintenance.settings.maintenance_button_icon'), 'name' => 'maintenance_button_icon', 'value' => setting('maintenance_button_icon')])
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-4">{{ __('global.save') }}</button>
        </form>
@endsection
