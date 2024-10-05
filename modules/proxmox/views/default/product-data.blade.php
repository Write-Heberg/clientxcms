<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
<h2 class="text-lg font-semibold mb-4">{{ __('proxmox::messages.data.os') }}</h2>

<div class="grid grid-cols-{{ $inAdmin ? 2 : 4 }} sm:grid-cols-{{ $inAdmin ? 2 : 4 }} gap-2">
    @foreach ($oses as $ose)
        <div>
        <label for="ose-{{ $ose->id }}" class="flex p-3 block w-full bg-white border border-gray-200 rounded-lg text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400">
            <span class="dark:text-gray-400 font-semibold">
                <div class="flex justify-center items-center mb-4 ml-2 w-15 h-15 rounded-lg bg-gray-100 dark:bg-gray-900 lg:h-12 lg:w-12">
            <div class="text-2xl text-gray-600 dark:text-gray-400" style="font-size: 15px;">
                {!! $ose->svg() !!}
            </div>
        </div>
                {{ $ose->name }}
            </span>
            <input type="radio" name="ose" value="{{ $ose->id }}" {{ $ose->id == ($data['ose'] ?? $loop->first) ? 'checked' : '' }} class="shrink-0 ms-auto mt-0.5 border-gray-200 rounded-full text-indigo-600 focus:ring-indigo-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-gray-800 dark:border-gray-700 dark:checked:bg-indigo-600 dark:checked:border-indigo-600 dark:focus:ring-offset-gray-800" id="ose-{{ $ose->id }}">
        </label>
</div>
    @endforeach
        @foreach ($templates as $template)
            <div>
                <label for="template-{{ $template->id }}" class="flex p-3 block w-full bg-white border border-gray-200 rounded-lg text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400">
            <span class="dark:text-gray-400 font-semibold">
                <div class="flex justify-center items-center mb-4 ml-2 w-15 h-15 rounded-lg bg-gray-100 dark:bg-gray-900 lg:h-12 lg:w-12">
            <div class="text-2xl text-gray-600 dark:text-gray-400" style="font-size: 15px;">
                {!! $template->svg() !!}
            </div>
        </div>
                {{ $template->name }}

            </span>
                    <input type="radio" name="template" value="{{ $template->id }}" {{ $template->id == ($data['template'] ?? ($loop->first && $oses->isEmpty())) ? 'checked' : '' }} class="shrink-0 ms-auto mt-0.5 border-gray-200 rounded-full text-indigo-600 focus:ring-indigo-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-gray-800 dark:border-gray-700 dark:checked:bg-indigo-600 dark:checked:border-indigo-600 dark:focus:ring-offset-gray-800" id="template-{{ $template->id }}">
                </label>
            </div>
        @endforeach
</div>
@if ($errors->has('ose') || $errors->has('template'))
    <div class="col-span-4">
        <p class="text-red-500 text-xs italic">{{ $errors->first('ose') ?: $errors->first('template') }}</p>
    </div>
@endif

@include('shared/input', [
    'label' => __('proxmox::messages.hostname'),
    'name' => 'hostname',
    'value' => old('hostname', $hostname),
])
@if ($password)
    <h2 class="text-lg font-semibold mb-2 mt-4">{{ __('proxmox::messages.data.security') }}</h2>

    @include('shared/password', [
        'label' => __('global.password'),
        'name' => 'password',
        'generate' => true,
        'value' => old('password', $data['password'] ?? null),
    ])
@endif
@if ($sshkeys)
    @include('shared/textarea', [
        'label' => __('proxmox::messages.sshkeys') . ' (' . __('global.optional') . ')',
        'name' => 'sshkeys',
        'type' => 'sshkeys',
        'value' => old('sshkeys', $data['sshkeys'] ?? null),
    ])
@endif
