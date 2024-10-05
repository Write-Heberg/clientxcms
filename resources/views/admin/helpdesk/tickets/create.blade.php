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
@section('style')
    <link rel="stylesheet" href="{{ Vite::asset('resources/global/css/simplemde.min.css') }}">
@endsection
@section('scripts')
    <script src="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js"></script>
    <script src="{{ Vite::asset('resources/global/js/mdeditor.js') }}"></script>
@endsection
@section('content')

    <div class="max-w-[85rem] py-5 lg:py-7 mx-auto">
        <div class="mx-auto">
            @include('admin/shared/alerts')
            @if ($hasCustomer)
                <form method="POST" action="{{ route($routePath . '.store') }}?customer_id={{ $hasCustomer }}" enctype="multipart/form-data">
                    @else
                        <form>
                            @endif
                    <div class="card">
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
                        @if ($hasCustomer)
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @csrf
                                <div class="col-span-2 sm:col-span-1">
                                    @include("admin/shared/input", ["name" => "subject", "label" => __("client.support.subject")])
                                </div>
                                <div class="col-span-2 sm:col-span-1">
                                    @include("admin/shared/select", ["name" => "priority", "label" => __("client.support.priority"), "options" => $priorities, 'value' => old('priority')])
                                </div>
                                <div  class="col-span-2 sm:col-span-1">
                                    @include("admin/shared/select", ["name" => "related_id", "label" => __("client.support.create.relatedto"), "options" => $related, 'value' => old('related_id', 'none')])
                                </div>
                                <div  class="col-span-2 sm:col-span-1">
                                    <label for="department_id" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-400 mt-2">{{ __('client.support.department') }}</label>
                                    <div class="relative mt-2">
                                        <select data-hs-select='{
      "toggleTag": "<button type=\"button\"><span class=\"me-2\" data-icon></span><span class=\"text-gray-800 dark:text-gray-200\" data-title></span></button>",
      "toggleClasses": "hs-select-disabled:pointer-events-none hs-select-disabled:opacity-50 relative py-3 px-4 pe-9 flex items-center text-nowrap w-full cursor-pointer bg-white border border-gray-200 rounded-lg text-start text-sm focus:border-blue-500 focus:ring-blue-500 before:absolute before:inset-0 before:z-[1] dark:bg-gray-700 dark:border-gray-700 dark:text-gray-400 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600",
      "dropdownClasses": "mt-2 z-50 w-full max-h-[300px] p-1 space-y-0.5 bg-white border border-gray-200 rounded-lg overflow-hidden overflow-y-auto dark:bg-slate-900 dark:border-gray-700",
      "optionClasses": "py-2 px-4 w-full text-sm text-gray-800 cursor-pointer hover:bg-gray-100 rounded-lg focus:outline-none focus:bg-gray-100 dark:bg-slate-900 dark:hover:bg-slate-800 dark:text-gray-400 dark:focus:bg-slate-800",
      "optionTemplate": "<div><div class=\"flex items-center\"><div class=\"me-2\" data-icon></div><div class=\"font-semibold text-gray-800 dark:text-gray-200\" data-title></div></div><div class=\"mt-1.5 text-sm text-gray-500\" data-description></div></div>"
    }' class="hidden" name="department_id">
                                            <option value="">Choose</option>
                                            @foreach($departments as $department)

                                                <option value="{{ $department->id }}" {{ old('department_id', $currentdepartment) == $department->id ? 'selected' : '' }} data-hs-select-option='{
        "description": "{{ $department->description }}",
        "icon": "<i class=\"{{$department->icon}}\"></i>"
        }'>{{ $department->name }}</option>
                                            @endforeach
                                        </select>

                                        <div class="absolute top-1/2 end-3 -translate-y-1/2">
                                            <svg class="flex-shrink-0 w-3.5 h-3.5 text-gray-500 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-span-2">
                                    <label for="editor" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-400 mt-2">{{ __('global.content') }}</label>
                                    <textarea id="editor" name="content">{{ old('content') }}</textarea>
                                @if ($errors->has('content'))
                                        @foreach ($errors->get('content') as $error)
                                            <div class="text-red-500 text-sm mt-2">
                                                {{ $error }}
                                            </div>
                                        @endforeach
                                    @endif

                                        <div class="mt-2">
                                            @include('admin/shared/file2', ['name' => 'attachments', 'label' => __('client.support.attachments'), 'help' => __('client.support.attachments_help', ['size' => setting('helpdesk_attachments_max_size'), 'types' => formatted_extension_list(setting('helpdesk_attachments_allowed_types'))])])
                                        </div>
                                </div>
                            </div>
                        </div>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                <div>
                                        @include('admin/shared/search-select', ['name' => 'customer_id', 'label' => __('admin.services.customer'), 'options' => $customers, 'value' => 1])

                                    </div>

                                    <div>
                                        <label for="department_id" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-400 mt-2">{{ __('client.support.department') }}</label>
                                        <div class="relative mt-2">
                                            <select data-hs-select='{
      "toggleTag": "<button type=\"button\"><span class=\"me-2\" data-icon></span><span class=\"text-gray-800 dark:text-gray-200\" data-title></span></button>",
      "toggleClasses": "hs-select-disabled:pointer-events-none hs-select-disabled:opacity-50 relative py-3 px-4 pe-9 flex items-center text-nowrap w-full cursor-pointer bg-white border border-gray-200 rounded-lg text-start text-sm focus:border-blue-500 focus:ring-blue-500 before:absolute before:inset-0 before:z-[1] dark:bg-gray-700 dark:border-gray-700 dark:text-gray-400 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600",
      "dropdownClasses": "mt-2 z-50 w-full max-h-[300px] p-1 space-y-0.5 bg-white border border-gray-200 rounded-lg overflow-hidden overflow-y-auto dark:bg-slate-900 dark:border-gray-700",
      "optionClasses": "py-2 px-4 w-full text-sm text-gray-800 cursor-pointer hover:bg-gray-100 rounded-lg focus:outline-none focus:bg-gray-100 dark:bg-slate-900 dark:hover:bg-slate-800 dark:text-gray-400 dark:focus:bg-slate-800",
      "optionTemplate": "<div><div class=\"flex items-center\"><div class=\"me-2\" data-icon></div><div class=\"font-semibold text-gray-800 dark:text-gray-200\" data-title></div></div><div class=\"mt-1.5 text-sm text-gray-500\" data-description></div></div>"
    }' class="hidden" name="department_id">
                                                <option value="">Choose</option>
                                                @foreach($departments as $department)
                                                    <option value="{{ $department->id }}" {{ old('department_id', $currentdepartment) == $department->id ? 'selected' : '' }} data-hs-select-option='{
        "description": "{{ $department->description }}",
        "icon": "<i class=\"{{$department->icon}}\"></i>"
        }'>{{ $department->name }} </option>
                                                @endforeach
                                            </select>

                                            <div class="absolute top-1/2 end-3 -translate-y-1/2">
                                                <svg class="flex-shrink-0 w-3.5 h-3.5 text-gray-500 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                </form>
        </div>
    </div>

@endsection
