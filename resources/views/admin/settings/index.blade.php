<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin.layouts.admin')
@section('title', __('admin.settings.title'))
@section('content')
    @foreach($cards as $card)
    <div class="py-3 sm:py-6 card">
        <h4 class="font-semibold uppercase text-gray-600 dark:text-gray-400">
            {{ __($card->name) }}
        </h4>
        <p class="mb-2 font-semibold text-gray-600 dark:text-gray-40">{{ __($card->description) }}</p>

        <div class="grid gap-2 sm:grid-cols-2 md:grid-cols-3">
            <!-- Card -->
            @foreach ($card->items as $item)

            <a {{ !$item->isActive() ? 'disabled="true"' : '' }} class="{{ !$item->isActive() ? 'provisioning-tab-disabled' : '' }} bg-white p-4 transition duration-300 rounded-lg hover:bg-gray-100 dark:bg-slate-900 dark:border-gray-800 dark:hover:bg-white/[.05] dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600" href="{{ !$item->isActive() ? '#':  $item->url() }}">
                <div class="flex">
                    <div class="mt-1.5 flex justify-center flex-shrink-0 rounded-s-xl">
                        <i class="w-5 h-5 text-gray-800 dark:text-gray-200 {{ $item->icon }}" style="font-size: 25px"></i>
                    </div>

                    <div class="grow ms-6">
                        <h3 class="text-sm font-semibold text-indigo-600 dark:text-indigo-600">
                            {{ __($item->name) }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-500">
                            {{ __($item->description, ['name' => __($item->name)]) }}
                        </p>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endforeach
    @endsection
