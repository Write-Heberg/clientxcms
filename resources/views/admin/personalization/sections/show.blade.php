<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin/layouts/admin')
@section('title', __('personalization.sections.show.title'))
@section('content')
    <div class="max-w-[85rem] py-5 lg:py-7 mx-auto">
        @include('admin/shared/alerts')
        <div class="flex flex-col">
            <div class="-m-1.5 overflow-x-auto">
                <div class="p-1.5 min-w-full inline-block align-middle">
                    <form method="POST" action="{{ route('admin.personalization.sections.update', ['section' => $item]) }}">
                        @method('PUT')
                        @csrf
                    <div class="card">
                        <div class="card-heading">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                                    {{ __('personalization.sections.show.title') }}
                                </h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('personalization.sections.show.subheading') }}
                                </p>
                            </div>
                            <div class="mt-4 flex items-center space-x-1 sm:mt-0">
                                <button class="btn btn-primary">
                                    {{ __('admin.updatedetails') }}
                                </button>
                            </div>
                            </div>
                        <div class="card-body">
                            <div class="grid gap-4">
                                <div>
                                    @include('admin/shared/select', [
                                        'label' => __('admin.themes.title'),
                                        'name' => 'theme_uuid',
                                        'value' => $item->theme_uuid,
                                        'options' => $themes
                                    ])
                                </div>

                                <div>
                                    @include('admin/shared/select', [
                                        'label' => __('personalization.sections.fields.url'),
                                        'name' => 'url',
                                        'value' => $item->url,
                                        'options' => $pages
                                    ])
                                </div>
                                <div>
                                    @include('admin/shared/textarea', [
                                        'label' => __('personalization.sections.content'),
                                        'name' => 'content',
                                        'value' => $content,
                                        'rows' => 20,
                                    ])
                                </div>
                                <div>
                                    @include('admin/shared/checkbox', [
                                        'label' => __('personalization.sections.fields.active'),
                                        'name' => 'is_active',
                                        'checked' => $item->is_active,
                                    ])
                                </div>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
</div>

@endsection
