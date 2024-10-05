<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('layouts/auth')
@section('title', __('auth.login.title'))
@section('content')
    <div class="p-4 sm:p-7">
        <div class="text-center">
            <h1 class="block text-2xl font-bold text-gray-800 dark:text-white">{{ __('client.profile.2fa.heading') }}</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('client.profile.2fa.subheading') }}
            </p>
        </div>
        @include('shared.alerts')

        <div class="mt-5">

            <form method="POST" action="{{ route('auth.2fa') }}">

                @include("shared.input", ["name" => "2fa", "type" => "text", "label" => __('client.profile.2fa.code')])
                @include('shared.captcha')
                @csrf

                <button type="submit" class="btn-primary block w-full mt-2">
                    {{ __('auth.login.login') }}</button>
            </form>

        </div>
    </div>
@endsection
