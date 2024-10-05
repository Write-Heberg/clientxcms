<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin/settings/sidebar')
@section('title',  __($translatePrefix . '.show.title', ['name' => $item->name]))
@section('setting')

    <div class="max-w-[85re m] mx-auto">

    <form method="POST" class="card" action="{{ route($routePath . '.update', ['subdomain' => $item]) }}">
                        <div class="card-heading">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                                    {{ __($translatePrefix . '.show.title', ['name' => $item->domain]) }}
                                </h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ __($translatePrefix. '.show.subheading', ['date' => $item->created_at->format('d/m/y')]) }}
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
                            @include('shared/input', ['name' => 'domain', 'label' => __($translatePrefix . '.subdomain'), 'value' => $item->domain])
                    </form>
                </div>
@endsection
