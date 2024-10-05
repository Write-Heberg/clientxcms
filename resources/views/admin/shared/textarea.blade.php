<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@php $rand = rand(1, 999); @endphp
@if(isset($label))
<label for="{{ $name }}{{ $rand }}" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-400 mt-2">{{ $label }}</label>
@endif
<div class="mt-2">
    <textarea @if (isset($disabled)) disabled="" @endif @if (isset($rows)) rows="{{ $rows }}" @endif name="{{ $name }}" id="{{ $name }}{{ $rand }}" rows="{{ $rows ?? 3 }}" class="input-text @error($name) border-red-500 @enderror">@if (isset($Inverifiedvalue)) {!! $Inverifiedvalue !!}@else{{ $value ?? old($name) }}@endif</textarea>
    @error($name)
    <span class="mt-2 text-sm text-red-500">
            {{ $message }}
        </span>
    @enderror

    @if (isset($help))
        <p class="text-sm text-gray-500 mt-2">{{ $help }}</p>
    @endif
</div>
