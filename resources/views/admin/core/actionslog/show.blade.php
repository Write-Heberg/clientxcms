<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin/layouts/admin')
@section('title',  __($translatePrefix . '.show.title', ['name' => $item->username()]))
@section('content')

    <div class="max-w-[85rem] py-5 lg:py-7 mx-auto">
    @include('admin/shared/alerts')
    <div class="flex flex-col">
        <div class="-m-1.5 overflow-x-auto">
            <div class="p-1.5 min-w-full inline-block align-middle">
                <div class="card-heading">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                                {{ __($translatePrefix . '.show.title', ['name' => $item->username()]) }}
                            </h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __($translatePrefix. '.show.subheading', ['date' => $item->created_at != null ?  $item->created_at->format('d/m/y H:i:s') : 'None']) }}
                            </p>
                        </div>
                    </div>
                <div class="card">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            @include('admin/shared/input', ['name' => 'username', 'label' => __($translatePrefix . '.user'), 'value' => $item->username()])
                        </div>
                        <div>
                            @include('admin/shared/input', ['name' => 'action', 'label' => __($translatePrefix . '.action'), 'value' => $item->action])
                        </div>
                        <div>
                            @include('admin/shared/input', ['name' => 'model', 'label' => __($translatePrefix . '.model'), 'value' => $item->model])
                        </div>

                        <div>
                            @include('admin/shared/input', ['name' => 'model_id', 'label' => __($translatePrefix . '.model_id'), 'value' => $item->model_id])
                        </div>
                        <div>
                            @include('admin/shared/textarea', ['name' => 'payload', 'label' => __($translatePrefix . '.payload'), 'value' => json_encode($item->payload)])
                        </div>
                    </div>
                    @if ($item->entries->count() > 0)
                    <div class="flex flex-col mt-3">
                        <div class="flex flex-col">
                            <div class="-m-1.5 overflow-x-auto">
                                <div class="p-1.5 min-w-full inline-block align-middle">
                                    <div class="overflow-hidden">
                                        <div class="border rounded-lg overflow-hidden dark:border-gray-700">
                                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                                <thead>
                                                <tr>
                                                    <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                                                        {{ __($translatePrefix . '.attribute') }}</th>
                                                    <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                                                        {{ __($translatePrefix . '.oldvalue') }}</th>
                                                    <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">{{ __($translatePrefix . '.newvalue') }}</th>
                                                </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                                @foreach ($item->entries as $entry)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-neutral-200">
                                                        {{ $entry->attribute }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">{{ $entry->old_value }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">{{ $entry->new_value }}</td>
                                                </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
