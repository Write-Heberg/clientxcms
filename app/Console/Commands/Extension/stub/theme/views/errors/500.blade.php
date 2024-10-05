<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('layouts/front')
@section('title', __('errors.500.title'))
@section('content')
    <div class="max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
        <div class="max-w-2xl mx-auto text-center mb-10 lg:mb-14">
            <h2 class="text-indigo-600 font-bold text-7xl dark:text-white">500</h2>
            <h2 class="font-bold text-3xl xl:text-7xl lg:text-6xl md:text-5xl mt-5 dark:text-white">{{ __('errors.500.title') }}</h2>
            <p class="text-gray-400 font-medium text-sm md:text-xl lg:text-2xl mt-8">{{ __('errors.500.description') }}</p>
        </div>
        <div class="max-w-2xl mx-auto text-center">
        <a href="{{ URL::previous() }}" class="py-3 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-indigo-100 text-indigo-800 hover:bg-indigo-200 disabled:opacity-50 disabled:pointer-events-none mt-10">{{ __('errors.404.back') }}</a>
        </div>
    </div>
@endsection
