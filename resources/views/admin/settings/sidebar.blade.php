<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin.layouts.admin')
@section('content')
    @include('shared.alerts')

    <nav class="relative z-0 flex border rounded-xl overflow-hidden dark:border-slate-700 mb-2" aria-label="Tabs" role="tablist">
        @foreach ($current_card->items as $item)
            @if ($item->isSetting())
            <a href="{{ !$item->isActive() ? '#':  $item->url() }}" class="hs-tab-active:border-b-blue-600 hs-tab-active:text-gray-900 dark:hs-tab-active:text-white relative dark:hs-tab-active:border-b-blue-600 min-w-0 flex-1 bg-white first:border-s-0 border-s border-b-2 py-4 px-4 text-gray-500 hover:text-gray-700 text-sm font-medium text-center overflow-hidden hover:bg-gray-50 focus:z-10 focus:outline-none focus:text-blue-600 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-800 dark:border-l-slate-700 dark:border-b-slate-700 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-400 {{ $item->uuid == $current_item->uuid ? 'active' : '' }}" id="earn-title-{{ Str::slug($item->uuid) }}" data-hs-tab="#earn-tab-{{ Str::slug($item->uuid) }}" aria-controls="earn-tab-{{ Str::slug($item->uuid) }}" role="tab">
                {{ __($item->name) }}
            </a>
            @endif
        @endforeach
    </nav>
    <div id="setting">
        @yield('setting')
    </div>
    @yield('script')
    <script>
        let checkboxes = document.querySelectorAll('#setting input[type=checkbox]');

        checkboxes.forEach(function(checkbox) {
            checkbox.value = checkbox.checked ? "true" : "false";
            checkbox.addEventListener('change', function() {
                checkbox.value = checkbox.checked ? "true" : "false";
            });
        });
    </script>
@endsection
