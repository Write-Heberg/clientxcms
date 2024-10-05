<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin/layouts/admin')
@section('title',  __($translatePrefix . '.create.title'))
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.querySelectorAll('[id^="selectAll_"]');
            selectAll.forEach((element) => {
                element.addEventListener('change', function () {
                    const checkboxes = this.parentNode.parentNode.parentNode.querySelectorAll('input[type="checkbox"]');
                    checkboxes.forEach((checkbox) => {
                        checkbox.checked = this.checked;
                    });
                });
            });
        });
    </script>
@endsection
@section('content')
    @include('admin/shared/alerts')

    <div class="flex flex-col md:flex-row gap-4">
        <div class="flex flex-col">
            <div class="-m-1.5 overflow-x-auto">
                <div class="p-1.5 min-w-full inline-block align-middle">
                <form method="POST" action="{{ route($routePath . '.store') }}">
                    <div class="card">
                            @csrf
                    <div class="card-heading">
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

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                @include('admin/shared/input', ['name' => 'name', 'label' => __('global.name'), 'value' => old('name', $item->name)])
                                <div class="mt-2">
                                    @include('admin/shared/checkbox', ['name' => 'is_default', 'label' => __('admin.roles.is_default'), 'value' => 'true', 'checked' => old('is_default', $item->is_default)])
                                </div>
                            </div>

                            <div class="col-span-2">
                                @include('admin/shared/input', ['name' => 'level', 'label' => __($translatePrefix . '.level'), 'value' => old('level', $item->level), 'type' => 'number', 'help' => __('admin.roles.levelhelp')])
                            </div>
                            <div class="content-center mt-6">
                                @include('admin/shared/checkbox', ['name' => 'is_admin', 'label' => __('admin.roles.is_admin'), 'value' => 'true', 'checked' => old('is_admin', $item->is_admin)])
                                <p class="text-sm text-gray-500 mt-2">{{ __('admin.roles.admin_help') }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4 mt-2">
                            @foreach($permissions as $label => $row)
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">

                                        <div class="flex items-center">
                                            <input type="checkbox" value="{{ $value ?? 'true' }}"
                                                   class="shrink-0 mt-1 border-gray-200 rounded text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-gray-800 dark:border-gray-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800"
                                                   id="selectAll_{{ $label }}">
                                            <label for="selectAll_{{ $label }}" class="font-semibold text-gray-800 dark:text-gray-200 ms-3 mt-1">{{ __($label) }}</label>
                                        </div>
                                    </h3>
                                    @foreach($row as $permission)
                                        <div class="mb-1">
                                            @include('admin/shared/checkbox', ['name' => 'permissions[]', 'label' => __($permission->label), 'value' => $permission->id, 'checked' => in_array($permission->id, $item->permissions->pluck('id')->toArray() ?? old('permissions', []))])
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                </form>
                </div>
        </div>
    </div>

@endsection
