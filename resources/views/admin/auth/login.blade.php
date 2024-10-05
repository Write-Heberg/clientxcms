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
            <h1 class="block text-2xl font-bold text-gray-800 dark:text-white">{{ __('admin.login.title') }}</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('admin.login.subheading') }}</p>
        </div>

        <div class="mt-5">
            <form method="POST" action="{{ route('admin.login') }}">
            @include('shared.auth.login', ['forgotPasswordRoute' => 'admin.password.request', 'captcha' => true])
            </form>
        </div>
    </div>
@endsection
