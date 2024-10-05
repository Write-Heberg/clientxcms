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
        <h3 class="text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">{{ __('admin.dashboard.widgets.last_login') }}</h3>
    </div>
    <div class="-m-1.5 overflow-auto">
        <div class="p-1.5 min-w-full inline-block align-middle">
            <div class="border rounded-lg overflow-hidden dark:border-gray-700">
            <div class="overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                    <tr>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">{{ __('global.customer') }}</th>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">{{ __('admin.customers.show.login_ip') }}</th>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">{{ __('global.date') }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($accounts as $account)
                        <tr class="bg-white hover:bg-gray-50 dark:bg-slate-900 dark:hover:bg-slate-800">

                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-gray-200">
                            <a href="{{ route('admin.customers.show', ['customer' => $account]) }}">{{ $account->fullName }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200 blur">{{ $account->last_ip }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $account->last_login->format('d/m/y H:i:s') }}</span>
                        </td>
                    </tr>
                    @endforeach
                    @if ($accounts->count() == 0)
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

