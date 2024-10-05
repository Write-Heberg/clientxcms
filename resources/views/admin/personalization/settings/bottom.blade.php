<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin.settings.sidebar')
@section('title', __('personalization.bottom_menu.title'))
@section('setting')
    <div class="card">
        <h4 class="font-semibold uppercase text-gray-600 dark:text-gray-400">
            {{ __('personalization.bottom_menu.title') }}
        </h4>
        <p class="mb-2 font-semibold text-gray-600 dark:text-gray-400">
            {{ __('personalization.bottom_menu.help') }}
        </p>
        <form action="{{ route('admin.personalization.bottom_menu') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if ($errors->any())

                <div class="alert text-red-700 bg-red-100 mt-2" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                    @foreach ($errors->all() as $error)
                        {!! $error !!}<br/>
                    @endforeach
                </div>
            @endif
            @foreach ($menu->items as $i => $item)
                <div class="grid grid-cols-2 gap-4 grid-head">
                    <div>
                        @include('shared.input', ['name' => 'menu_items['.$i.'][name]', 'label' => __('global.name'), 'value' => $item['name']])
                    </div>
                    <div>
                        @include('shared.input', ['name' => 'menu_items['.$i.'][url]', 'label' => __('global.url'), 'value' => $item['url']])
                        <div class="flex items-end space-x-4 sm:mt-0">
                            <button class="btn btn-danger mt-2" onclick="event.preventDefault(); deleteRow(this)">
                                <svg class="flex-shrink-0 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                            </button>
                        </div>
                        </div>
                </div>
            @endforeach

            @include('admin/shared/textarea', ['name' => 'theme_footer_description', 'label' => __('personalization.theme.fields.theme_footer_description'), 'value' => nl2br(setting('theme_footer_description')), 'help' => __('personalization.theme.fields.theme_footer_description_help')])

            <div>
                @include('admin/shared/textarea', ['name' => 'theme_footer_topheberg', 'label' => __('personalization.theme.fields.theme_footer_topheberg'), 'Inverifiedvalue' => setting('theme_footer_topheberg')])
            </div>
            <button type="button" class="btn btn-secondary mt-2" id="addMenu">{{ __('personalization.addmenu') }}</button>

            <button type="submit" class="btn btn-primary mt-2">{{ __('global.save') }}</button>
        </form>
@endsection
@section('script')
    <script>
        const deleteRow = (element) => {
            element.closest('.grid').remove();
        };
        const insertAfter = (referenceNode, newNode) => {
            referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
        }
        document.getElementById('addMenu').addEventListener('click', function() {
            let menu = document.querySelector('#setting form');
            let items = menu.querySelectorAll('.grid-head');
            let last = items[items.length - 1];
            let clone = last.cloneNode(true);
            let inputs = clone.querySelectorAll('input');
            let labels = clone.querySelectorAll('label');
            inputs.forEach(function(input) {
                input.value = '';
                input.name = input.name.replace(/\[(\d+)\]/, function(match, number) {
                    return '[' + (parseInt(number) + 1) + ']';
                });
                input.id = input.id.replace(/\[(\d+)\]/, function(match, number) {
                    return '[' + (parseInt(number) + 1) + ']';
                });
            });
            labels.forEach(function(label) {
                label.htmlFor = label.htmlFor.replace(/\[(\d+)\]/, function(match, number) {
                    return '[' + (parseInt(number) + 1) + ']';
                });
            });

            insertAfter(last, clone);
        });
    </script>
@endsection
