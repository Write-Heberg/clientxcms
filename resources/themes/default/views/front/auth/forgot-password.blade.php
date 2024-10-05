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

            <h1 class="block text-2xl font-bold text-gray-800 dark:text-white">{{ __('auth.forgot.heading') }}</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('auth.register.already') }}
                <a class="text-blue-600 decoration-2 hover:underline font-medium dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600" href="{{ route('login') }}">
                    {{ __('auth.login.login') }}
                </a>
            </p>
        </div>
        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="space-y-12">
                <div class="pb-6">
                    <div>
                        @include("shared.input", ["name" => "email", "label" => __('global.email'), "type" => "email"])
                    </div>
                    <div class="border-b border-gray-900/10 pb-4 mt-4">
                        @include('shared.captcha')
                    </div>

                    <button class="btn-primary block w-full">
                        {{ __('auth.forgot.btn') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
