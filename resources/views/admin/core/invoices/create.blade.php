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
    <script src="{{ Vite::asset('resources/global/js/clipboard.js') }}" type="module"></script>
    <script src="{{ Vite::asset('resources/global/js/flatpickr.js') }}" type="module"></script>
@endsection
@section('content')

    <div class="max-w-[85rem] py-5 lg:py-7 mx-auto">
        <div class="sm:w-11/12 lg:w-3/4 mx-auto">
            @include('shared/alerts')
                <form method="POST" action="{{ route($routePath .'.store') }}">
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
                                {{ __('admin.invoices.create.btn') }}
                            </button>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex flex-col">
                            @include('shared/search-select', ['name' => 'customer_id', 'label' => __('admin.services.customer'), 'options' => $customers, 'value' => 1])
                        </div>
                        <div>
                            @include("shared/select", ['name' => 'currency', 'label' => __('admin.invoices.currency'), 'options' => $currencies, 'value' => 'EUR'])
                        </div>
                        <div>
                            @include("shared/flatpickr", ['name' => 'date_due', 'label' => __('client.invoices.due_date'), 'value' => $date_due])
                        </div>
                    </div>
                    </div>
                </form>
        </div>
    </div>
@endsection
