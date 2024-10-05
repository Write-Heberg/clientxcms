<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin/settings/sidebar')
@section('title',  __($translatePrefix . '.create.title'))

@section('setting')

    <div class="max-w-[85re m] mx-auto">
            <form method="POST" class="card" action="{{ route($routePath . '.store') }}">
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
                @csrf
                        @include('shared/input', ['name' => 'domain', 'label' => __($translatePrefix . '.subdomain'), 'value' => $item->domain])
            </form>
        </div>

@endsection
