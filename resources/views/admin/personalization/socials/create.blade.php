<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin/settings/sidebar')

@section('title',  __($translatePrefix . '.create.title', ['name' => $item->fullname]))
@section('setting')
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
                        @include('shared/input', ['name' => 'url', 'label' => __('global.url'), 'value' => old('url', $item->url)])
                    </div>
                    <div>
                        @include('shared/input', ['name' => 'icon', 'label' => __($translatePrefix . '.icon'), 'value' => old('icon', $item->icon), 'help' => __('personalization.icon_help')])
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
