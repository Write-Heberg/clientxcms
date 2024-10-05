<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
<div class="flex flex-col">
    <div class="card-heading">
        <h3 class="text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">{{ __('admin.dashboard.widgets.services_expiration') }}</h3>
    </div>
    <div class="-m-1.5 overflow-x-auto">
        <div class="p-1.5 min-w-full inline-block align-middle">
            <div class="overflow-hidden">
                <div class="border rounded-lg overflow-hidden dark:border-gray-700">

                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                    <tr>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">{{ __('global.name') }}</th>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">{{ __('store.price') }}</th>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">{{ __('client.services.expire_date') }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($services as $service)
                        <tr class="bg-white hover:bg-gray-50 dark:bg-slate-900 dark:hover:bg-slate-800">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-gray-200">
                            <a href="{{ route('admin.services.show', ['service' => $service]) }}">{{ $service->name }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ formatted_price($service->price, $service->currency) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                            <x-service-days-remaining expires_at="{{ $service->expires_at }}" state="{{ $service->status }}" date_at="{{ $service->status == 'expired' ? $service->expires_at->format('d-m-y') : ($service->suspended_at ? $service->suspended_at->format('d-m-y') : '') }}"></x-service-days-remaining>
                        </td>
                    </tr>
                    @endforeach
                    @if ($services->count() == 0)
                        <tr class="bg-white hover:bg-gray-50 dark:bg-slate-900 dark:hover:bg-slate-800">

                        <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ __('global.no_results') }}</td>
                    </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
</div>

