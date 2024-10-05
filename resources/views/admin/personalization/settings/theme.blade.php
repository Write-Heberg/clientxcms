<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin.settings.sidebar')
@section('title', __('personalization.theme.title'))
@section('setting')
    <div class="card">
        <h4 class="font-semibold uppercase text-gray-600 dark:text-gray-400">
            {{ __('personalization.theme.title') }}
        </h4>
        <p class="mb-2 font-semibold text-gray-600 dark:text-gray-400">
            {{ __('personalization.theme.description') }}
        </p>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Card -->
                    @foreach ($themes as $theme)
                    <div class="group flex flex-col h-full bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-slate-900 dark:border-slate-700 dark:shadow-slate-700/70">
                        <div class="h-52 flex flex-col justify-center items-center rounded-t-xl bg-primary" @if ($theme->hasScreenshot()) style="background: url('{{ Vite::asset($theme->screenshotUrl()) }}'); background-size: cover;" @endif>
                        </div>
                        <div class="p-4 md:p-6">
                            @if ($currentTheme->uuid == $theme->uuid)

        <span class="block mb-1 text-xs font-semibold uppercase text-green-600 dark:text-green-500">
          {{ __('extensions.settings.enabled') }}
        </span>
                            @else
                                <span class="block mb-1 text-xs font-semibold uppercase text-red-500 dark:text-red-500">
                                    {{ __('extensions.settings.disabled') }}
                                </span>
                            @endif
                            <h3 class="text-xl font-semibold text-gray-800 dark:text-slate-300 dark:hover:text-white">
                                {{ $theme->name }}
                            </h3>
                            <p class="mt-3 text-gray-500 dark:text-slate-500">
                                {{ $theme->description }}
                            </p>
                        </div>

                            @if ($currentTheme->uuid == $theme->uuid)
                                <div class="flex border-t border-gray-200 divide-x divide-gray-200 dark:border-slate-700 dark:divide-slate-700">
                                    <button class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-es-xl bg-white text-gray-800 shadow-sm hover:bg-gray-50 focus:outline-none focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-slate-700 dark:text-white dark:hover:bg-slate-800 dark:focus:bg-slate-800 {{ !$theme->hasConfig() ? 'cursor-not-allowed' : '' }}" {!!  !$theme->hasConfig() ? 'disabled' : 'data-hs-overlay="#theme-config"' !!}>
                                        {{ __('personalization.config.button') }}
                                    </button>
                                </div>
                            @else
                            <form action="{{ route('admin.personalization.switch_theme', ['theme' => $theme->uuid]) }}" method="POST" class="mt-auto flex border-t border-gray-200 divide-x divide-gray-200 dark:border-slate-700 dark:divide-slate-700">
                            @csrf
                                <a class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-es-xl bg-white text-gray-800 shadow-sm hover:bg-gray-50 focus:outline-none focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-slate-700 dark:text-white dark:hover:bg-slate-800 dark:focus:bg-slate-800" href="{{ $theme->demoUrl() }}">
                                    {{ __('personalization.demo') }}
                                </a>
                                <button type="submit" class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-ee-xl bg-green-100 text-green-800 shadow-sm hover:bg-green-50 focus:outline-none focus:bg-green-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-slate-700 dark:text-white dark:hover:bg-slate-800 dark:focus:bg-slate-800">
                                    <i class="bi bi-arrow-repeat"></i>
                                    {{ __('extensions.settings.enable') }}
                                </button>
                            </form>
                                @endif
                    </div>
                    <!-- End Card -->
                    @endforeach
                </div>
            <!-- End Card Blog -->
                @method('PUT')
            </div>

    <div id="theme-config" class="hs-overlay hs-overlay-open:translate-x-0 hidden translate-x-full fixed top-0 end-0 transition-all duration-300 transform h-full max-w-xs w-full z-[80] bg-white border-s dark:bg-neutral-800 dark:border-neutral-700" role="dialog" tabindex="-1" aria-labelledby="theme-config-label">
        <div class="flex justify-between items-center py-3 px-4 border-b dark:border-neutral-700">
            <h3 id="theme-config-label" class="font-bold text-gray-800 dark:text-white">
                {{ __('personalization.config.title') }}
            </h3>
            <button type="button" class="size-8 inline-flex justify-center items-center gap-x-2 rounded-full border border-transparent bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-none focus:bg-gray-200 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:text-neutral-400 dark:focus:bg-neutral-600" aria-label="Close" data-hs-overlay="#theme-config">
                <span class="sr-only">{{ __('global.close') }}</span>
                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6 6 18"></path>
                    <path d="m6 6 12 12"></path>
                </svg>
            </button>
        </div>
        <div class="p-4">
                <form method="POST" action="{{ route('admin.personalization.config_theme', ['theme' => $currentTheme->uuid]) }}">
                    <p class="text-gray-800 dark:text-neutral-400">
                        @csrf
                    {!! $configHTML !!}
                </p>

                <button type="submit" class="btn btn-primary mt-2">{{ __('global.save') }}</button>
            </form>

        </div>
    </div>
@endsection
