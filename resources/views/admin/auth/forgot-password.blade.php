<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('layouts/auth')
@section('title', __('auth.forgot.title'))
@section('content')

    <div class="p-4 sm:p-7">
        <div class="text-center">
            @include('shared.alerts')

            <h1 class="block text-2xl font-bold text-gray-800 dark:text-white">{{ __('auth.forgot.title') }}</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('auth.forgot.subheading') }}
            </p>
        </div>
            <form method="POST" action="{{ route('admin.password.email') }}">
                @csrf
                <div class="space-y-12">
                    <div class="pb-6">
                        <div class="border-b border-gray-900/10 pb-6">
                            @include("shared.input", ["name" => "email", "label" => __('global.email'), "type" => "email"])
                        </div>
                        <button class="btn-primary block w-full">
                            {{ __('auth.forgot.btn') }}
                        </button>
                    </div>
                </div>
            </form>
    </div>
@endsection
