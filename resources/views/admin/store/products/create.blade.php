<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin/layouts/admin')
@section('title',  __($translatePrefix . '.create.title', ['name' => $item->fullname]))
@section('style')
    <link rel="stylesheet" href="{{ Vite::asset('resources/global/css/editor.scss') }}">
@endsection
@section('scripts')
    <script src="{{ Vite::asset('resources/global/js/editor.js') }}" type="module"></script>
    <script>
        const showmorepricingbtn = document.getElementById('showmorepricingbtn');
        const calculatorBtn = document.getElementById('calculatorBtn');
        const table = document.getElementById('pricingtable');
        const hidden = table.querySelectorAll('.hidden');
        showmorepricingbtn.addEventListener('click', function (e) {
            e.preventDefault();
            Array.from(hidden).map((el) => el.classList.toggle('hidden'));
        });

        function showmorepricingbtn_hidden() {
            const filter = Array.from(hidden).filter((el) => el.classList.contains('hidden'));
            if (filter.length > 0) {
                return true;
            }
            return false;
        }
        calculatorBtn.addEventListener('click', function (e) {
            e.preventDefault();
            const percentage = document.querySelector('input[name="percentage"]').value;
            const monthlyPrice = document.querySelector('input[data-months="1"][name$="[price]"]').value;
            const monthlySetup = document.querySelector('input[data-months="1"][name$="[setup]"]').value;

            const prices = document.querySelectorAll('input[name^="pricing"]:not([name*="onetime"])');
            if (percentage > 100 || percentage < 0 || percentage === '') {
                return;
            }
            prices.forEach((price) => {
                const months = price.getAttribute('data-months');
                const setup = document.querySelector('input[data-months="' + months +'"][name$="[setup]"]');
                if (months === '1' || months === '0.5') return;
                if (months === '24' || months === '36' && showmorepricingbtn_hidden()) return;
                if (price.value === '' || setup.value === '') {
                    if (price.value === '') {
                        price.value = 0;
                    }
                    if (setup.value === '') {
                        setup.value = '';
                    }
                }

                const newPrice = monthlyPrice * months - (monthlyPrice * months) *(percentage / 100);
                const newSetup = monthlySetup * months -  (monthlySetup * months) * (percentage / 100);
                price.value = newPrice.toFixed(2);
                setup.value = newSetup.toFixed(2);
            });
        });

    </script>
@endsection
@section('content')
    <div class="max-w-[85rem] py-5 lg:py-7 mx-auto">
        @include('admin/shared/alerts')
        <form method="POST" action="{{ route($routePath .'.store') }}" enctype="multipart/form-data">
        <div class="flex flex-col">
            <div class="-m-1.5 overflow-x-auto">
                <div class="p-1.5 min-w-full inline-block align-middle">
                    <div class="card">
                        <div class="card-heading">
                                @csrf
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                                    {{ __($translatePrefix . '.create.title') }}
                                </h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ __($translatePrefix. '.create.subheading') }}
                                </p>
                            </div>
                            <div class="mt-4 flex items-center space-x-4 sm:mt-0">
                                <button class="btn btn-primary">
                                    {{ __('admin.create') }}
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                @include('admin/shared/input', ['name' => 'name', 'label' => __('global.name'), 'value' => old('name', $item->name)])
                            </div>
                            <div>
                                @include('admin/shared/input', ['name' => 'stock', 'label' => __($translatePrefix . '.stock'), 'value' => old('stock', $item->stock)])
                            </div>
                            <div>
                                @include('admin/shared/select', ['name' => 'type', 'label' => __($translatePrefix . '.type'), 'value' => old('type', $item->type), 'options' => $types])
                            </div>
                            <div>
                                @include('admin/shared/status-select', ['name' => 'status', 'label' => __('global.status'), 'value' => old('status', $item->status)])
                                @include('admin/shared/select', ['name' => 'group_id', 'label' => __($translatePrefix . '.group'), 'value' => old('group_id', $item->group_id), 'options' => $groups])
                                @include('admin/shared/input', ['name' => 'sort_order', 'label' => __('global.sort_order'), 'value' => old('sort_order', $item->sort_order)])
                                <div class="mt-2">
                                    @include('admin/shared/checkbox', ['name' => 'pinned', 'label' => __('global.pinned'), 'checked' => old('pinned', $item->pinned)])
                                </div>
                            </div>

                            <div class="col-span-2">
                                @include('admin/shared/editor', ['name' => 'description', 'label' => __('global.description'), 'value' => old('description', $item->description)])

                            </div>
                        </div>
                    </div>

                    <div class="card mt-2">
                        <div class="card-body">
                            <div class="flex flex-col">
                                <div class="-m-1.5 overflow-x-auto">
                                    <div class="p-1.5 min-w-full inline-block align-middle">
                                        <a href="#" class="text-primary" id="showmorepricingbtn">{{ __($translatePrefix . '.showmorepricing') }}</a>
                                        <div class="overflow-hidden">
                                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="pricingtable">
                                                <thead>
                                                <tr>
                                                    <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                                                        <button class="btn btn-primary btn-sm" type="button" data-hs-overlay="#calculator"><i class="bi bi-calculator"></i></button>
                                                        {{ __($translatePrefix . '.tariff') }}
                                                    </th>
                                                    @foreach ($recurrings as $recurring)
                                                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase {{ $recurring['additional'] ?? false ? 'hidden' : '' }}">
                                                            {{ $recurring['translate'] }}
                                                        </th>
                                                    @endforeach
                                                </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-gray-200">
                                                        {{ __('store.price') }}
                                                    </td>
                                                    @foreach ($recurrings as $k => $recurring)

                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200 {{ $recurring['additional'] ?? false ? 'hidden' : '' }}">
                                                        @include('admin/shared/input', ['name' => 'pricing['. $k .'][price]','type' => 'number','step' => '0.01','min' => 0, 'value' => old('recurrings_' . $k . '_price', $pricing->{$k}), 'attributes' => ['data-months' => $recurring['months']]])
                                                    </td>
                                                    @endforeach
                                                </tr>
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-gray-200">
                                                        {{ __('store.fees') }}
                                                    </td>
                                                    @foreach ($recurrings as $k => $recurring)

                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200 {{ $recurring['additional'] ?? false ? 'hidden' : '' }}">
                                                            @include('admin/shared/input', ['name' => 'pricing['. $k .'][setup]', 'step' => '0.01','min' => 0, 'value' => old('recurrings_' . $k . '_setup', $pricing->{$k . '_setup'}), 'attributes' => ['data-months' => $recurring['months']]])
                                                        </td>

                                                    @endforeach
                                                </tr>

                                                </tbody>
                                            </table>
                                        </div>
                                        @if ($errors->has('pricing'))
                                            <p class="text-red-500 text-xs italic mt-2">
                                                {{ $errors->first('pricing') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </form>
    </div>
    <div id="calculator" class="hs-overlay hs-overlay-open:translate-x-0 hidden translate-x-full fixed top-0 end-0 transition-all duration-300 transform h-full max-w-xs w-full z-[80] bg-white border-s dark:bg-gray-800 dark:border-gray-700" tabindex="-1">
        <div class="flex justify-between items-center py-3 px-4 border-b dark:border-gray-700">
            <h3 class="font-bold text-gray-800 dark:text-white">
                {{ __($translatePrefix . '.calculator.title') }}
            </h3>
            <button type="button" class="flex justify-center items-center size-7 text-sm font-semibold rounded-full border border-transparent text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:text-white dark:hover:bg-gray-700 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600" data-hs-overlay="#calculator">
                <span class="sr-only">{{ __('global.close')  }}</span>
                <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <div class="p-4">
            <p class="text-gray-800 dark:text-gray-400">
                {{ __($translatePrefix . '.calculator.subheading') }}
                @include('admin/shared/input', ['name' => 'percentage', 'help' => __($translatePrefix . '.calculator.help'), 'label' => __($translatePrefix . '.calculator.percent'), 'value' => 5])
            </p>
            <button type="button" class="btn btn-primary mt-2" id="calculatorBtn" data-hs-overlay="#calculator">
                {{ __($translatePrefix . '.calculator.apply') }}
            </button>
        </div>
    </div>
@endsection
