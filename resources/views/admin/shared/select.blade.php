<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
<label for="{{ $name }}" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-400 mt-2">{{ $label }}</label>
<div class="mt-2">
    <select name="{{ $name }}" id="{{ $name }}" class="input-text" @foreach($attributes ?? [] as $k => $v) {{ $k }}="{{ $v }}"@endforeach >
        @foreach($options as $_value => $option)
            <option value="{{ $_value }}"{{ $value == $_value || old($name) == $_value ? ' selected' : '' }}>{{ $option }}</option>
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
