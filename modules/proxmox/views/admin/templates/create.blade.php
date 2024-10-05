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
@section('content')

    <div class="max-w-[85rem] py-5 lg:py-7 mx-auto">
        <div class="sm:w-11/12 lg:w-3/4 mx-auto">
            @include('shared/alerts')
                <form method="POST" class="card" action="{{ route($routePath . '.store') }}">
                    <div class="card-heading">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                                {{ __($translatePrefix . '.create.title') }}
                            </h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __($translatePrefix. '.create.description') }}
                            </p>
                        </div>

                        <div class="mt-4 flex items-center space-x-4 sm:mt-0">
                            <button class="btn btn-primary">
                                {{ __('admin.create') }}
                            </button>
                        </div>
                    </div>
                    @csrf
                    @include('admin/shared/input', [
                        'label' => __('proxmox::messages.templates.name'),
                        'name' => 'name',
                        'value' => old('name'),
                    ])
                    @foreach ($servers as $server)
                        <h3 class="font-semibold uppercase text-gray-600 dark:text-gray-400 mt-2">{{ $server->name }}</h3>

                        @include('admin/shared/select', [
                            'label' => __('proxmox::messages.templates.vmids'),
                            'attributes' => ['multiple' => true],
                            'name' => 'vmids['. $server->id .'][]',
                            'options' => $templates[$server->id] ?? [],
                            'value' => old('vmids['. $server->id .']', [])
                        ])
                        @endforeach
                </form>
            </div>
    </div>

@endsection
