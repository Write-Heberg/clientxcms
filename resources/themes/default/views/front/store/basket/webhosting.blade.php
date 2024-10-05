<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
<h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200"> {{ __('provisioning.subdomains.choose_domain') }}</h3>
@if ($subdomains->isNotEmpty())

    <div>
        <label for="domain_subdomain" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-400 mt-2">{{ __('provisioning.subdomains.use_subdomain', ['app_name' => config('app.name')]) }}</label>
        <div class="sm:flex rounded-lg shadow-sm">
            <input type="text" class="py-3 px-4 pe-11 input-text" name="domain_subdomain" value="{{ $data['domain_subdomain'] ?? '' }}">
            <select type="text" class="py-3 px-4 pe-11 input-text" name="subdomain">
                @foreach($subdomains as $subdomain)
                    <option value="{{ $subdomain->domain }}"{{ $data['subdomain'] ?? '' == $subdomain->domain ? ' selected' : '' }}>{{ $subdomain->domain }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="py-3 flex items-center text-xs text-gray-400 uppercase before:flex-[1_1_0%] before:border-t before:border-gray-200 before:me-6 after:flex-[1_1_0%] after:border-t after:border-gray-200 after:ms-6 dark:text-gray-500 dark:before:border-gray-600 dark:after:border-gray-600">
        {{ trans("global.or") }}</div>

@endif
@include("shared/input", ['name' => 'domain', 'label' => __($subdomains->isNotEmpty() ? 'provisioning.subdomains.use_owndomain' : 'provisioning.domain'), 'value' => $data->domain ?? ''])
