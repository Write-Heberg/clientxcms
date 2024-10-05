<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
<!doctype html>
<html class="{{is_darkmode() ? 'dark' : '' }}" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    {{-- ... --}}
    <title>@yield('title') {{ setting('seo_site_title') }}</title>
    @yield('styles')
    @vite('resources/themes/default/js/app.js')
    @vite('resources/themes/default/css/app.scss')
    {!! app('seo')->head() !!}
    {!! app('seo')->favicon() !!}
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
</head>
<body class="dark:bg-slate-900 bg-gray-100 flex h-full items-center py-16">
<main class="w-full {{ in_array(Route::current()->getName(), ['register', 'socialauth.finish']) ? 'max-w-6xl' : 'max-w-md' }} mx-auto p-6">
    <div class="mt-7 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <img class="mx-auto h-12 w-auto mt-4" src="{{ setting('app_logo_text') }}" alt="{{ setting('app_name') }}">
        @yield('content')
    </div>
</main>
@yield('scripts')
</body>
</html>
