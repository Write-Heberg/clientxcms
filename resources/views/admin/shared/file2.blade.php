<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@if(isset($label))
    <label for="{{ $name }}" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-400 sr-only">{{ $label }}</label>
@endif
<input
    type="file"
    multiple
    class="block w-full border border-gray-200 shadow-sm rounded-lg text-sm focus:z-10 focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-gray-900 dark:border-gray-700 dark:text-neutral-400
    file:bg-gray-50 file:border-0
    file:me-4
    file:py-3 file:px-4
    dark:file:bg-gray-700 dark:file:text-gray-400"
    name="{{ $name }}[]"
/>    @error($name)
<span class="mt-2 text-sm text-red-500">
            {{ $message }}
        </span>
@enderror
@if (isset($help))
    <p class="text-sm text-gray-500 mt-2">{{ $help }}</p>
@endif
@for ($i = 0; $i < 6; $i++)
    @error("attachments.$i")
        <div class="text-red-500 text-sm mt-2">
            {{ $message }}
        </div>
    @enderror
@endfor
