<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('layouts/front')
@section('title', setting('theme_home_title_meta', setting('app.name')))
@section('content')
    <div class="max-w-[85rem] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-7 lg:gap-x-8 xl:gap-x-12 lg:items-center">
            <div class="lg:col-span-3">
                <h1 class="block text-3xl font-bold text-gray-800 sm:text-4xl md:text-5xl lg:text-6xl dark:text-white">
                    {{ setting('theme_home_title', setting('app.name')) }}</h1>
                <p class="mt-3 text-lg text-gray-800 dark:text-gray-400">{{ setting('theme_home_subtitle', "Hébergeur français de qualité utilisant la nouvelle version Next GEN") }}</p>
            </div>
            <div class="lg:col-span-4 mt-15 lg:mt-0">
                <img class=rounded-xl" src="{{ setting('theme_home_image') }}" alt="">
            </div>
        </div>
    </div>
    {!! render_theme_sections() !!}
    @endsection
