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
        <div class="sm:w-11/12 lg:w-3/4 mx-auto">
            @include('admin/shared/alerts')

            <form method="POST" class="card" action="{{ route($routePath . '.update', ['template' => $item]) }}">
                        <div class="card-heading">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                                    {{ __($translatePrefix . '.show.title', ['name' => $item->name]) }}
                                </h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ __($translatePrefix. '.show.description', ['date' => $item->created_at->format('d/m/y')]) }}
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
                @include('admin/shared/input', [
                    'label' => __('proxmox::messages.templates.name'),
                    'name' => 'name',
                    'value' => old('name', $item->name ?? null),
                ])
                @foreach ($servers as $server)
                    <h3 class="font-semibold uppercase text-gray-600 dark:text-gray-400 mt-2">{{ $server->name }}</h3>
                    @include('admin/shared/search-select-multiple', [
                        'label' => __('proxmox::messages.templates.vmids'),
                        'name' => 'vmids['. $server->id .'][]',
                        'attributes' => ['multiple' => true],
                        'options' => $templates[$server->id] ?? [],
                        'value' => old('server_id', $vmids[$server->id] ?? [])
                    ])
                @endforeach
                            <input type="hidden" name="id" value="{{ $item->id }}">
                    </form>
        </div>
    </div>

@endsection
