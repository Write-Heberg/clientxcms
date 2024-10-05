<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin.settings.sidebar')
@section('title', __('personalization.home.title'))
@section('setting')
    <div class="card">
        <h4 class="font-semibold uppercase text-gray-600 dark:text-gray-400">
            {{ __('personalization.home.title') }}
        </h4>
        <p class="mb-2 font-semibold text-gray-600 dark:text-gray-400">
            {{ __('personalization.home.description') }}
        </p>

        <form action="{{ route('admin.settings.personalization.home') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    @include('admin/shared/input', ['name' => 'theme_home_title', 'label' => __('personalization.home.fields.theme_home_title'), 'value' => setting('theme_home_title', setting('app.name'))])
                </div>
                <div>
                    @include('admin/shared/input', ['name' => 'theme_home_subtitle', 'label' => __('personalization.home.fields.theme_home_subtitle'), 'value' => setting('theme_home_subtitle', "Hébergeur français de qualité utilisant la nouvelle version Next Gen de CLIENTXCMS.")])
                </div>
                <div>
                    @include('admin/shared/input', ['name' => 'theme_home_title_meta', 'label' => __('personalization.home.fields.theme_home_title_meta'), 'value' => setting('theme_home_title_meta', setting('app.name')), 'help' => __('personalization.home.fields.theme_home_title_meta_help')])

                    <div class="mt-2 mb-2">
                        @include('admin/shared/checkbox', ['name' => 'theme_home_enabled', 'label' => __('personalization.home.fields.theme_home_enabled'), 'checked' => setting('theme_home_enabled')])
                    </div>
                </div>
                <div>

                    @include('admin/shared/file', ['name' => 'theme_home_image', 'label' => __('personalization.home.fields.theme_home_image'), 'canRemove' => true])
                </div>
                @method('PUT')
            </div>
            <button type="submit" class="btn btn-primary mt-2">{{ __('global.save') }}</button>
        </form>
@endsection
