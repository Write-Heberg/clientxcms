<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
<label for="{{ $name }}" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-400 mt-2">{{ $label }}</label>
<div class="mt-2 relative">
    <select multiple name="{{ $name }}" @foreach($attributes ?? [] as $k => $v) {{ $k }}="{{ $v }}"@endforeach id="{{ $name }}" data-hs-select='{
      "hasSearch": true,
      "searchPlaceholder": "{{ __('global.search') }}",
      "searchClasses": "block w-full text-sm border-gray-200 rounded-lg focus:border-blue-500 focus:ring-blue-500 before:absolute before:inset-0 before:z-[1] dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600 py-2 px-3",
      "searchWrapperClasses": "bg-white p-2 -mx-1 sticky top-0 dark:bg-slate-900",
      "toggleTag": "<button type=\"button\"></button>",
            "toggleClasses": "hs-select-disabled:pointer-events-none hs-select-disabled:opacity-50 relative py-3 px-4 pe-9 flex text-nowrap w-full cursor-pointer input-text before:absolute before:inset-0 before:z-[1]",
"toggleCountText": "selected",
      "dropdownClasses": "mt-2 z-50 w-full max-h-[300px] p-1 space-y-0.5 bg-white border border-gray-200 rounded-lg overflow-hidden overflow-y-auto dark:bg-slate-900 dark:border-gray-700",
      "optionClasses": "py-2 px-4 w-full text-sm text-gray-800 cursor-pointer hover:bg-gray-100 rounded-lg focus:outline-none focus:bg-gray-100 dark:bg-slate-900 dark:hover:bg-slate-800 dark:text-gray-200 dark:focus:bg-slate-800",
      "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"hidden hs-selected:block\"><svg class=\"flex-shrink-0 w-3.5 h-3.5 text-blue-600 dark:text-blue-500\" xmlns=\"http:.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><polyline points=\"20 6 9 17 4 12\"/></svg></span></div>"

      }' class="hidden" >

        @foreach($options as $_value => $option)
            <option value="{{ $_value }}"{{ in_array($_value, $value) || old($name) == $_value ? ' selected' : '' }}>{{ $option }}</option>
        @endforeach
    </select>
    @error($name)
    <span class="mt-2 text-sm text-red-500">
            {{ $message }}
        </span>
    @enderror

        @if (isset($help))
            <p class="text-sm text-gray-500 mt-2">{{ $help }}</p>
        @endif
</div>
