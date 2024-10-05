<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>

@if (isset($value) && isset($checked) == false)
    @php
        $checked = $value == 'true';
    @endphp
@endif
@php $rand = rand(1, 999); @endphp

    <div class="flex">
        <input type="checkbox" value="{{ $value ?? 'true' }}"
               class="shrink-0 mt-0.5 border-gray-200 rounded text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-gray-800 dark:border-gray-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800"
               id="{{ $name }}{{ $rand }}" name="{{ $name }}" {{ $checked ?? false ? 'checked' : '' }}>
        @if ($label)
        <label for="{{ $name }}{{ $rand }}" class="text-sm text-gray-500 ms-3 dark:text-gray-400">{{ $label }}</label>
        @endif
        @if ($errors->has($name))
            <div class="invalid-feedback">{{ $errors->first($name) }}</div>
        @endif
    </div>
