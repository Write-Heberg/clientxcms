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
@section('content')
<div class="max-w-[85rem] py-5 lg:py-7 mx-auto">
    <div class="mx-auto">
        @include('admin/shared/alerts')
        <form method="POST" class="card" action="{{ route($routePath . '.update', ['department' => $item]) }}">
            <div class="card-heading">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                        {{ __($translatePrefix . '.show.title', ['name' => $item->name]) }}
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __($translatePrefix. '.show.subheading', ['date' => $item->created_at == null ? 'None' : $item->created_at->format('d/m/y')]) }}
                    </p>
                </div>

                <div class="mt-4 flex items-center space-x-4 sm:mt-0">
                    <button class="btn btn-primary">
                        {{ __('admin.updatedetails') }}
                    </button>
                </div>
            </div>
            @method('PUT')
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <div class="flex flex-col">
                    @include('admin/shared/input', ['name' => 'name', 'label' => __('global.name'), 'value' => old('name', $item->name)])
                </div>
                <div class="flex flex-col">
                    @include('admin/shared/input', ['name' => 'icon','help' => __('admin.helpdesk.departments.icon_help'), 'label' => __('admin.helpdesk.departments.icon'), 'value' => old('icon', $item->icon)])
                </div>

                <div class="flex flex-col">
                    @include('admin/shared/textarea', ['name' => 'description', 'label' => __('global.description'), 'value' => old('description', $item->description)])
                </div>
                <input type="hidden" name="id" value="{{ $item->id }}">
            </div>
        </form>
    </div>
</div>
@endsection
