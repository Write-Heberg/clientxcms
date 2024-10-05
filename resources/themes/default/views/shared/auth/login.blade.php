<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>

    @csrf
    <div class="grid gap-y-4">
        <div>
            <label for="email" class="block block text-sm font-medium leading-6 text-gray-900 dark:text-gray-400 mt-2">{{ trans("global.email") }}</label>
            @include("shared.input", ["name" => "email", "type" => "email"])
        </div>
        <div>
            <div class="flex justify-between items-center">
                <label for="password" class="block block text-sm font-medium leading-6 text-gray-900 dark:text-gray-400 mt-2">{{ trans("global.password") }}</label>
                <a class="text-sm text-indigo-600 decoration-2 hover:underline font-medium dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600" href="{{ route($forgotPasswordRoute ?? 'password.email') }}">{{ __('auth.forgot.forgot_password') }}</a>
            </div>
            <div class="relative">
                @include("shared.input", ["name" => "password", "type" => "password"])
            </div>
        </div>
        <div class="flex items-center">
            @include('shared/checkbox', ['label' => __('auth.login.remember'), 'name' => 'remember'])
        </div>
        @if (isset($redirect))
            <input type="hidden" name="redirect" value="{{ $redirect }}">
        @endif
        @if (isset($captcha))
            @include('shared.captcha')
        @endif
        <button type="submit" class="btn-primary block w-full">
            {{ __('auth.login.login') }}</button>
    </div>
