<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin/layouts/admin')
@section('title',  __($translatePrefix . '.show.title', ['name' => $item->name]))
@php($products = $item->products()->orderBy('sort_order')->get())
@php($group = $item)
@section('scripts')
    <script src="{{ Vite::asset('resources/global/js/sort.js') }}" type="module"></script>
@endsection
@section('content')
    <div class="max-w-[85rem] py-5 lg:py-7 mx-auto">
        @include('admin/shared/alerts')
        <div class="flex flex-col">
            <div class="-m-1.5 overflow-x-auto">
                <div class="p-1.5 min-w-full inline-block align-middle">
                    <form class="card" method="POST" action="{{ route($routePath .'.update', ['group' => $item]) }}" enctype="multipart/form-data">
                        <div class="card-heading">
                                @csrf
                            <input type="hidden" name="id" value="{{ $item->id }}">
                            @method('PUT')
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                                    {{ __($translatePrefix . '.show.title', ['name' => $item->name]) }}
                                </h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ __($translatePrefix. '.show.subheading', ['date' => $item->created_at->format('d/m/y')]) }}
                                </p>
                            </div>
                            <div class="mt-4 flex items-center space-x-4 sm:mt-0">
                                <button class="btn btn-primary">
                                    {{ __('admin.updatedetails') }}
                                </button>
                                @if (staff_has_permission('manage_metadata'))

                                <button class="btn btn-secondary text-left" type="button" data-hs-overlay="#metadata-overlay">
                                    <i class="bi bi-database mr-2"></i>
                                    {{ __('admin.metadata.title') }}
                                </button>
                                @endif
                                <a href="{{ $group->route() }}" target="_blank" class="btn btn-white text-sm">
                                    {{ __($translatePrefix . '.see_group') }}
                                </a>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                @include('admin/shared/input', ['name' => 'name', 'label' => __('global.name'), 'value' => old('name', $item->name)])
                            </div>

                            <div>
                                @include('admin/shared/input', ['name' => 'slug', 'label' => __('global.slug'), 'value' => old('slug', $item->slug)])
                            </div>
                                <div>
                                @include('admin/shared/select', ['name' => 'parent_id', 'label' => __($translatePrefix . '.parent_id'), 'value' => old('parent_id', $item->parent_id == null ? 'none' : $item->parent_id), 'options' => $groups])

                                </div>
                            <div>
                                @include('admin/shared/status-select', ['name' => 'status', 'label' => __('global.status'), 'value' => old('status', $item->status)])
                                @include('admin/shared/input', ['name' => 'sort_order', 'label' => __('global.sort_order'), 'value' => old('sort_order', $item->sort_order)])

                            </div>
                            <div>
                                @include('admin/shared/textarea', ['name' => 'description', 'label' => __('global.description'), 'value' => old('description', $item->description)])
                                <div class="mt-2">
                                    @include('admin/shared/checkbox', ['name' => 'pinned', 'label' => __('global.pinned'), 'checked' => old('pinned', $item->pinned)])
                                </div>
                            </div>

                            <div>
                                @include('admin/shared/file', ['name' => 'image', 'label' => __($translatePrefix . '.image'), 'help' => __('admin.blanktochange'), 'canremove' => true])
                            </div>
                        </div>
                    </form>
                    @if(staff_has_permission('manage_products'))
<div class="card mt-2">
    <div class="card-heading">
        <div>

        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
            {{ __('global.products') }}
        </h2>
        <h3 class="text-sm text-gray-600 dark:text-gray-400">
            {{ __($translatePrefix . '.order_products') }}
        </h3>
        </div>
        <button type="button" class="btn btn-primary" id="saveButton">{{ __('global.save') }}</button>

    </div>
                    <ul  data-url="{{ route('admin.groups.sort') }}" is="sort-list" data-button="#saveButton" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:items-center">
                    @foreach($products->where('status', 'active')->chunk(3) as $row)
                            @foreach($row as $product)
                                <li id="{{ $product->id }}" class="sortable-item">
                                    @if($product->pinned)
                                        @include($viewPath .'.products.pinned')
                                    @else
                                        @include($viewPath .'.products.product')
                                    @endif
                                </li>
                            @endforeach
                    @endforeach
                    </ul>
                </div>
                </div>
                @endif

            </div>
    </div>
    @include('admin/metadata/overlay', ['item' => $group])

@endsection
