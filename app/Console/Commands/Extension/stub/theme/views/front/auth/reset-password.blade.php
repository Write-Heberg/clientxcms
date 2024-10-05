<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('layouts/auth')
@section('title', __('auth.reset.title'))
@section('content')


    <div class="p-4 sm:p-7">
        <div class="text-center">
            @include('shared.alerts')

            <h1 class="block text-2xl font-bold text-gray-800 dark:text-white">{{ __('auth.reset.heading') }}</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('auth.register.already') }}
                <a class="text-blue-600 decoration-2 hover:underline font-medium dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600" href="{{ route('login') }}">
                    {{ __('auth.login.login') }}
                </a>
            </p>
        </div>
        <form method="POST" action="{{ route('password.store') }}">
                <input type="hidden" name="token" value="{{ $request->route('token') }}">
                @csrf
                <div class="space-y-12">
                    <div class="pb-6">
                        <div class="border-b border-gray-900/10 pb-6">
                            @include("shared.input", ["name" => "email", "label" => __('global.email'), "type" => "email", "value" => $request->get('email')])
                            <div class="mt-3">
                            @include("shared.input", ["name" => "password", "label" => __('global.password'), "type" => "password"])
                            </div>
                            <div class="mt-3">
                            @include("shared.input", ["name" => "password_confirmation", "label" => __('global.password_confirmation'), "type" => "password"])
                            </div>
                        </div>
                        <button class="btn-primary block w-full">
                            {{ __('auth.reset.btn') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
@endsection
