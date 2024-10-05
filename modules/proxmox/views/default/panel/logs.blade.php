<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>

<div class="max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
    @include('shared/alerts')
    <div class="flex flex-col">
        <div class="-m-1.5 overflow-x-auto">
            <div class="p-1.5 min-w-full inline-block align-middle">
                <div class="card">
                    <span class="flex items-center text-1xl uppercase font-medium text-gray-900 dark:text-white mb-4">
    <span class="flex w-4 h-4 @if($vps->isRunning()) vps-running @elseif($vps->isStopped()) vps-stopped @else vps-warning @endif  rounded-full mr-1.5 flex-shrink-0"></span>
    <p class="ml-2">
        {{ __('proxmox::messages.logs.title') }} - {{ $vps->hostname() }}
    </p>
</span>
                    <div class="border rounded-lg overflow-hidden dark:border-gray-700">
                        <div class="table-responsive">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                            <tr>
                                <th scope="col" class="px-6 py-3 text-start">
                                    <div class="flex items-center gap-x-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-gray-200">
                      {{ __('proxmox::messages.logs.action') }}
                    </span>
                                    </div>
                                </th>

                                <th scope="col" class="px-6 py-3 text-start">
                                    <div class="flex items-center gap-x-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-gray-200">
                      {{ __('proxmox::messages.logs.by') }}
                    </span>
                                    </div>
                                </th>



                                <th scope="col" class="px-6 py-3 text-start">
                                    <div class="flex items-center gap-x-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-gray-200">
                      {{ __('global.date') }}
                    </span>
                                    </div>
                                </th>
                            </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @if (count($logs) == 0)

                                <tr class="bg-white hover:bg-gray-50 dark:bg-slate-900 dark:hover:bg-slate-800">
                                    <td class="h-px w-px whitespace-nowrap" colspan="4">
                    <span class="block px-6 py-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('global.no_results') }}</span>
                    </span>
                                    </td>
                                </tr>
                            @endif
                            @foreach($logs as $log)
                                <tr class="bg-white hover:bg-gray-50 dark:bg-slate-900 dark:hover:bg-slate-800">
                                    <td class="h-px w-px whitespace-nowrap">

                    <span class="block px-6 py-2">
                      <span class="text-sm text-gray-600 dark:text-gray-400">{{ __($log->type) }}</span>
                    </span>
                                    </td>
                                    <td class="h-px w-px whitespace-nowrap">
                    <span class="block px-6 py-2">
                      <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('proxmox::messages.logs.users')[$log->user] }}</span>
                    </span>
                                    </td>
                                    <td class="h-px w-px whitespace-nowrap">
                    <span class="block px-6 py-2">
                      <span class="text-sm text-gray-600 dark:text-gray-400">{{ $log->created_at->format('d/m/y H:i') }}</span>
                    </span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        </div>
                    </div>

                    <div class="py-1 px-4 mx-auto">
                        {{ $logs->links('shared.layouts.pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
