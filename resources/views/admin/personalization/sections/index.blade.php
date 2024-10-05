<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin/layouts/admin')
@section('title', __('personalization.sections.title'))
@section('scripts')
<script src="{{ Vite::asset('resources/global/js/sort.js') }}" type="module"></script>
@endsection
@section('content')
    <div class="max-w-[85rem] py-5 lg:py-7 mx-auto">
        @include('admin/shared/alerts')
        @if (!empty($pages))
        <div class="flex flex-col">
            <div class="-m-1.5 overflow-x-auto">
                <div class="p-1.5 min-w-full inline-block align-middle">

                    <div class="grid grid-cols-6 gap-4">

                        <div class="col-span-6 md:col-span-1">
                            <div class="card">
                                <div class="flex flex-wrap">
                                    <nav class="flex flex-col space-y-2" role="tablist" aria-orientation="horizontal">
                                        @foreach($pages as $uuid => $item)
                                            <button type="button" class="hs-tab-active:border-blue-500 hs-tab-active:text-blue-600 dark:hs-tab-active:text-blue-600 py-1 pe-4 inline-flex items-center gap-x-2 border-e-2 border-transparent text-sm whitespace-nowrap text-gray-500 hover:text-blue-600 focus:outline-none focus:text-blue-600 disabled:opacity-50 disabled:pointer-events-none dark:text-neutral-400 dark:hover:text-blue-500 {{ $loop->first ? 'active' : '' }}" id="page-detail-item-item-{{ $uuid }}" aria-selected="true" data-hs-tab="#page-detail-item-{{ $uuid }}" aria-controls="page-detail-item-{{ $uuid }}" role="tab">
                                                {{ $item['title'] }}
                                            </button>
                                        @endforeach
                                    </nav>
                                </div>
                            </div>
                            <div class="card">
                                <div class="flex flex-wrap">
                                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 hidden sm:block">
                                        {{ __('personalization.sections.title') }}
                                    </h3>
                                    <nav class="flex flex-col space-y-2" role="tablist" aria-orientation="horizontal">

                                    @foreach ($sectionTypes as $sectionType)
                                        <button type="button" class="hs-tab-active:border-blue-500 hs-tab-active:text-blue-600 dark:hs-tab-active:text-blue-600 py-1 pe-4 inline-flex items-center gap-x-2 border-e-2 border-transparent text-sm whitespace-nowrap text-gray-500 hover:text-blue-600 focus:outline-none focus:text-blue-600 disabled:opacity-50 disabled:pointer-events-none dark:text-neutral-400 dark:hover:text-blue-500 {{ $loop->first ? 'active' : '' }}" id="page-detail-item-item-{{ $sectionType->uuid }}" aria-selected="true" data-hs-tab="#page-section-type-{{ $sectionType->uuid }}" aria-controls="page-section-type-{{ $sectionType->uuid }}" role="tab">
                                            {{ $sectionType->name() }}
                                        </button>
                                    @endforeach
                                    </nav>
                                </div>
                            </div>
                        </div>


                            <div class="col-span-6 md:col-span-5">
                                <div class="card">
                        <div class="card-heading">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                                    {{ __('personalization.sections.pages.title') }}
                                </h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('personalization.sections.pages.subheading') }}
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-wrap">
                            @foreach ($pages as $uuid => $item)
                                <div id="page-detail-item-{{ $uuid }}" {!! !$loop->first ? 'class="hidden"' : ''  !!} role="tabpanel" aria-labelledby="page-detail-item-{{ Str::slug($uuid) }}">
                                   <ul class="w-full" data-button="#saveButton-{{ $uuid }}" data-url="{{ route('admin.personalization.sections.sort') }}" is="sort-list">
                                       @foreach ($item['sections'] as $section)
                                           <li class="flex items-center justify-between py-2 px-4 dark:border-neutral-700 rounded-lg mb-2 sortable-item" id="{{ $section->id }}">
                                               <div class="group relative flex flex-col h-full bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-slate-900 dark:border-slate-700 dark:shadow-slate-700/70">
                                                   <div class="hs-dropdown absolute top-3 left-3 z-10">
                                                       <button id="hs-dropdown-custom-icon-trigger" type="button" class="hs-dropdown-toggle flex justify-center items-center size-9 text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 focus:outline-none focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:p-3 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-800 dark:focus:bg-neutral-800 p-3" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                                                           <i class="bi bi-three-dots"></i>
                                                       </button>
                                                       <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-60 bg-white shadow-md rounded-lg p-1 space-y-0.5 mt-2 dark:bg-neutral-800 dark:border dark:border-neutral-700" role="menu" aria-orientation="vertical" aria-labelledby="hs-dropdown-custom-icon-trigger">
                                                           <a class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700" href="{{ route($routePath . '.show', ['section' => $section]) }}" {{ !$section->isModifiable() ? 'disabled="true"' : '' }}>
                                                               {{ __('global.edit') }}
                                                           </a>
                                                           <form method="POST" action="{{ route($routePath . '.clone', ['section' => $section]) }}">
                                                               @csrf
                                                               <button class="flex items-center gap-x-3.5 py-2 w-full px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700">
                                                                   {{ __('global.clone') }}
                                                               </button>
                                                           </form>

                                                           <form method="POST" action="{{ route($routePath . '.restore', ['section' => $section]) }}">
                                                               @csrf
                                                               <button class="flex w-full items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700">
                                                                   {{ __('personalization.sections.restore') }}
                                                               </button>
                                                           </form>
                                                           <form method="POST" action="{{ route($routePath . '.switch', ['section' => $section]) }}">
                                                               @csrf
                                                           <button class="flex w-full items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700">
                                                               {{ $section->is_active ? __('personalization.sections.disable') : __('personalization.sections.enable') }}
                                                           </button>
                                                           </form>
                                                           <form method="POST" action="{{ route($routePath . '.destroy', ['section' => $section]) }}">
                                                               @method('DELETE')
                                                               @csrf
                                                           <button class="flex items-center w-full gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700">
                                                               {{ __('global.delete') }}
                                                           </button>
                                                           </form>

                                                       </div>
                                                   </div>
                                                   @if ($section->isPremium())
                                                       <button type="button" class="absolute top-3 right-3 z-10 btn btn-sm">
                                                           <i class="bi bi-star text-warning text-lg"></i>
                                                       </button>
                                                    @endif
                                                   @if (!$section->is_active)
                                                       <button type="button" class="absolute top-3 right-3 z-10 btn btn-sm">
                                                           <i class="bi bi-eye-slash text-danger text-lg"></i>
                                                       </button>
                                                    @endif
                                                   <img src="{{ $section->thumbnail() }}" class="rounded-b-xl" style="background-size: cover; background-repeat: no-repeat;">
                                               </div>
                                           </li>
                                       @endforeach
                                   </ul>
                                    <button type="button" class="btn btn-primary" id="saveButton-{{ $uuid }}">{{ __('global.save') }}</button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                        <div class="card">
                            @foreach ($sectionTypes as $sectionType)
                                <div id="page-section-type-{{ Str::slug($sectionType->uuid) }}" {!! !$loop->first ? 'class="hidden"' : ''  !!} role="tabpanel" aria-labelledby="page-section-type-{{ Str::slug($sectionType->uuid) }}">
                                    <ul class="w-full" >

                                        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                                            {{ __('personalization.sections.title') }}
                                        </h2>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('personalization.sections.types.subheading', ['name' => $sectionType->name()]) }}
                                        </p>
                                        @foreach ($sectionType->sections as $section)
                                            <li class="flex items-center justify-between py-2 px-4 dark:border-neutral-700 rounded-lg mb-2">
                                                <div class="group relative flex flex-col h-full bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-slate-900 dark:border-slate-700 dark:shadow-slate-700/70">
                                                    <div class="hs-dropdown absolute top-3 left-3 z-10">
                                                        <form method="POST" action="{{ route($routePath . '.clone_section', ['section' => $section->uuid]) }}">
                                                            @csrf
                                                       <button class="flex justify-center items-center size-9 text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 focus:outline-none focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-800 dark:focus:bg-neutral-800 p-2" type="submit">
                                                              <i class="bi bi-cloud-plus"></i>
                                                       </button>
                                                        </form>

                                                    </div>
                                                    <img src="{{ $section->thumbnail() }}" class="rounded-b-xl">
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endforeach
                        </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
        <div class="min-h-60 flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-700 dark:shadow-neutral-700/70">
            <div class="flex flex-auto flex-col justify-center items-center p-4 md:p-5">
                <svg class="size-10 text-gray-500 dark:text-neutral-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" x2="2" y1="12" y2="12"></line>
                    <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path>
                    <line x1="6" x2="6.01" y1="16" y2="16"></line>
                    <line x1="10" x2="10.01" y1="16" y2="16"></line>
                </svg>
                <p class="mt-2 text-sm text-gray-800 dark:text-neutral-300">
                    No sections found
                </p>
            </div>
        </div>
        @endif
    </div>
@endsection
