<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin.settings.sidebar')
@section('title', __('personalization.seo.title'))
@section('setting')
    <div class="card">
        <h4 class="font-semibold uppercase text-gray-600 dark:text-gray-400">
            {{ __('personalization.seo.title') }}
        </h4>
        <p class="mb-2 font-semibold text-gray-600 dark:text-gray-400">
            {{ __('personalization.seo.description') }}
        </p>

        <form action="{{ route('admin.settings.personalization.seo') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    @include('shared/input', ['label' => __('personalization.seo.fields.description'), 'name' => 'seo_description', 'value' => setting('seo_description')])
                </div>
                <div>
                    @include('shared/input', ['label' => __('personalization.seo.fields.keywords'), 'name' => 'seo_keywords', 'value' => setting('seo_keywords')])
                </div>
            @method('PUT')
                <div>
                    @include('shared/textarea', ['label' => __('personalization.seo.fields.headscripts'), 'name' => 'seo_headscripts', 'value' => setting('seo_headscripts'), 'rows' => 10])
                </div>
                <div>
                    @include('shared/textarea', ['label' => __('personalization.seo.fields.footscripts'), 'name' => 'seo_footscripts', 'value' => setting('seo_footscripts'), 'rows' => 10])
                </div>

                <div>
                    @include('admin/shared/input', ['name' => 'seo_site_title', 'label' => __('personalization.seo.fields.site_title'), 'value' => setting('seo_site_title'), 'help' => __('personalization.seo.fields.site_title_help')])
                </div>
                <div>
                    @include('shared/input', ['label' => __('personalization.seo.fields.themecolor'), 'type' => 'color', 'name' => 'seo_themecolor', 'value' => setting('seo_themecolor')])
                </div>
                <div>
                    @include('shared/checkbox', ['label' => __('personalization.seo.fields.disablereferencement'), 'name' => 'seo_disablereferencement', 'value' => setting('seo_disablereferencement', 'false')])
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3 ">{{ __('global.save') }}</button>
        </form>
@endsection
