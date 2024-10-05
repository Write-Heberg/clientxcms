<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin/layouts/admin')
@section('title',  __($translatePrefix . '.create.title', ['name' => $item->fullname]))
@section('content')
    <div class="max-w-[85rem] py-5 lg:py-7 mx-auto">
        @include('shared/alerts')
        <div class="flex flex-col">
            <div class="-m-1.5 overflow-x-auto">
                <div class="p-1.5 min-w-full inline-block align-middle">
                    <form class="card" method="POST" action="{{ route($routePath .'.store') }}" enctype="multipart/form-data">
                        <div class="card-heading">
                                @csrf
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                                    {{ __($translatePrefix . '.create.title') }}
                                </h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ __($translatePrefix. '.create.subheading') }}
                                </p>
                            </div>
                            <div class="mt-4 flex items-center space-x-4 sm:mt-0">
                                <button class="btn btn-primary">
                                    {{ __('admin.create') }}
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                @include('shared/input', ['name' => 'name', 'label' => __('global.name'), 'value' => old('name', $item->name)])
                            </div>
                            <div>
                                @include('shared/input', ['name' => 'slug', 'label' => __('global.slug'), 'value' => old('slug', $item->slug)])
                            </div>
                            <div>
                                @include('shared/select', ['name' => 'parent_id', 'label' => __($translatePrefix . '.parent_id'), 'value' => old('parent_id', $item->parent_id == null ? 'none' : ''), 'options' => $groups])
                            </div>
                            <div>
                                @include('shared/status-select', ['name' => 'status', 'label' => __('global.status'), 'value' => old('status', $item->status)])
                                @include('shared/input', ['name' => 'sort_order', 'label' => __('global.sort_order'), 'value' => old('sort_order', $item->sort_order)])
                            </div>

                            <div>
                                @include('shared/textarea', ['name' => 'description', 'label' => __('global.description'), 'value' => old('description', $item->description)])
                                <div class="mt-2">
                                    @include('shared/checkbox', ['name' => 'pinned', 'label' => __('global.pinned'), 'checked' => old('pinned', $item->pinned)])
                                </div>
                            </div>

                            <div>
                                @include('shared.file', ['name' => 'image', 'label' => __($translatePrefix . '.image'), 'help' => __('admin.blanktochange')])
                            </div>
                        </div>
                    </form>
            </div>
        </div>
    </div>
@endsection
