<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
    <!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full ">
<head>
    {{-- ... --}}
    <title>@yield('title') {{ setting('seo_site_title') }}</title>
    @yield('styles')
    @vite('resources/themes/default/css/app.scss')
    @vite('resources/themes/default/js/app.js')
    {!! app('seo')->head() !!}
    {!! app('seo')->favicon() !!}
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
</head>
<body class="{{is_darkmode() ? 'dark' : '' }} flex flex-col h-full">
<div class="dark:bg-gray-900 h-full">
    <main id="content" role="main" class="shrink-0">
        <div class="overflow-hidden">

            <header class="flex flex-wrap sm:justify-start sm:flex-nowrap z-50 w-full py-2.5 sm:py-4 bg-white border-b border-gray-200 text-sm py-3 sm:py-0 dark:bg-gray-800 dark:border-gray-700 print:hidden">
                <nav class="max-w-7xl flex basis-full items-center mx-auto px-4 sm:px-6 lg:px-8" aria-label="Global">
                    <div class="me-5 md:me-8">
                        @if (setting('theme_header_logo', false))
                            <a class="flex-none text-xl font-semibold dark:text-white dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600" href="/" aria-label="{{ setting('app_name') }}">
                                <img class="mx-auto h-10 w-auto" src="{{ setting('app_logo_text', asset('images/logo.png')) }}" alt="{{ setting('app_name') }}">
                            </a>
                        @else
                            <a class="flex-none text-xl font-semibold dark:text-white dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600" href="/" aria-label="{{ setting('app_name') }}">{{ setting('app_name') }}</a>
                        @endif
                    </div>
                    <div class="hs-overlay hs-overlay-open:translate-x-0 -translate-x-full fixed top-0 start-0 transition-all duration-300 transform h-full max-w-xs w-full z-[60] bg-white border-e basis-full grow sm:order-2 sm:static sm:block sm:h-auto sm:max-w-none sm:w-auto sm:border-r-transparent sm:transition-none sm:translate-x-0 sm:z-40 sm:basis-auto dark:bg-gray-800 dark:border-r-gray-700 sm:dark:border-r-transparent sm:block" tabindex="-1">
                        <div class="flex flex-col gap-y-4 gap-x-0 mt-5 sm:flex-row sm:items-center sm:justify-end sm:gap-y-0 sm:mt-0 sm:ps-7">
                            @foreach (app('theme')->getFrontLinks() as $link => $data)
                                <a class="font-medium sm:px-2 mr-3 {{ is_subroute($link) ? 'text-indigo-500 hover:text-indigo-400 dark:text-indigo-400 dark:hover:text-indigo-500' : 'text-gray-500 hover:text-gray-400 dark:text-gray-400 dark:hover:text-gray-500' }}" href="{{ $link }}">
                                    <i class="{{ $data['icon'] }} mr-1"></i> {{ $data['name'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-end ms-auto sm:justify-between sm:gap-x-3 sm:order-3">
                        <div class="flex flex-row items-center justify-end gap-2">
                            @include('shared.layouts.iconright')
                        </div>

                        <div class="sm:hidden">
                            <button type="button" class="hs-collapse-toggle size-9 flex justify-center items-center text-sm font-semibold rounded-lg text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:text-white dark:border-neutral-700 dark:hover:bg-neutral-700 p-1" data-hs-collapse="#navbar-menu" aria-controls="navbar-menu" aria-label="Toggle navigation">
                                <svg class="hs-collapse-open:hidden flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" x2="21" y1="6" y2="6"/><line x1="3" x2="21" y1="12" y2="12"/><line x1="3" x2="21" y1="18" y2="18"/></svg>
                                <svg class="hs-collapse-open:block hidden flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                            </button>
                        </div>
                    </div>
                </nav>
            </header>

            <div id="navbar-menu" class="dark:bg-gray-800 dark:border-gray-700 hs-collapse hidden overflow-hidden transition-all duration-300 basis-full grow" tabindex="-1">
                <div class="mx-auto flex flex-col gap-y-4 gap-x-0 my-5 ml-3 sm:flex-row sm:items-center sm:gap-y-0 sm:gap-x-7 sm:mt-0 sm:ps-7">
                    @foreach (app('theme')->getFrontLinks() as $link => $data)
                        <a class="font-medium sm:px-2 mr-3 {{ is_subroute($link) ? 'text-indigo-500 hover:text-indigo-400 dark:text-indigo-400 dark:hover:text-indigo-500' : 'text-gray-500 hover:text-gray-400 dark:text-gray-400 dark:hover:text-gray-500' }}" href="{{ $link }}">
                            <i class="{{ $data['icon'] }} mr-1"></i> {{ $data['name'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        @yield('content')

    </main>
    @include('layouts.footer')
</div>

@yield('scripts')
{!! app('seo')->foot() !!}
<form method="POST" action="{{ route('logout') }}" id="logout-form">
    @csrf
</form>

</body>
</html>
