<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin.settings.sidebar')
@section('title', __('personalization.primary.title'))
@section('setting')
    <div class="card">
        <h4 class="font-semibold uppercase text-gray-600 dark:text-gray-400">
            {{ __('personalization.primary.title') }}
        </h4>
        <p class="mb-2 font-semibold text-gray-600 dark:text-gray-400">
            {{ __('personalization.primary.description') }}
        </p>

        <form action="{{ route('admin.personalization.primary') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    @include('shared.input', ['type' => 'color','name' => 'theme_primary', 'label' => __('personalization.primary.fields.primary_color'), 'value' => $primary_color])
                </div>

                <div>
                    @include('shared.input', ['type' => 'color', 'name' => 'theme_secondary', 'label' => __('personalization.primary.fields.secondary_color'), 'value' => $secondary_color])
                </div>

                @method('PUT')
            </div>
            <p class="text-gray-500 mt-2">Cette fonctionnalité est encore en bêta pour voir le rendu sur votre espace client, veuillez contacter le support</p>
            <button type="submit" class="btn btn-primary mt-2">{{ __('global.save') }}</button>

            <!--
                        <p class="text-gray-500 mt-2">{{ __('personalization.primary.preview_save') }}</p>
            <a href="{{ route('admin.personalization.previewprimary') }}" class="btn btn-secondary mt-2" target="_blank">{{ __('personalization.primary.btnpreview') }} <i class="bi bi-box-arrow-up-right"></i></a>
            -->
        </form>
@endsection
