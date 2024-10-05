<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
<div class="card">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-3">

        {{ __($translatePrefix . '.show.emails') }}</h2>
<div class="border rounded-lg overflow-hidden dark:border-gray-700">

    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead>
        <tr>

            <th scope="col" class="px-6 py-3 text-start">
                <div class="flex items-center gap-x-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-gray-200">
                        {{ __('client.emails.subject') }}
                    </span>
                </div>
            </th>


            <th scope="col" class="px-6 py-3 text-start">
                <div class="flex items-center gap-x-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-gray-200">
                      {{ __('global.created') }}
                    </span>
                </div>
            </th>

        </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
        @if (count($emails) == 0)
            <tr class="bg-white hover:bg-gray-50 dark:bg-slate-900 dark:hover:bg-slate-800">
                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center">
                    <div class="flex flex-auto flex-col justify-center items-center p-2 md:p-3">
                        <p class="text-sm text-gray-800 dark:text-gray-400">
                            {{ __('global.no_results') }}
                        </p>
                    </div>
                </td>
        @endif
        @foreach($emails as $email)
            <tr class="bg-white hover:bg-gray-50 dark:bg-slate-900 dark:hover:bg-slate-800">
                <td class="h-px w-px whitespace-nowrap">
                    <span class="block px-6 py-2">
                      <span class="text-sm text-gray-600 dark:text-gray-400">
                          <a href="{{ route('admin.emails.show', ['email' => $email]) }}">
                          #{{ $email->id }} - {{ $email->subject }}</span>
                        </a>
                    </span>
                </td>
                <td class="h-px w-px whitespace-nowrap">
                    <span class="block px-6 py-2">
                      <span class="text-sm text-gray-600 dark:text-gray-400">{{ $email->created_at->format('d/m/y H:i') }}</span>
                    </span>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>

    <div class="py-1 px-4 mx-auto">
    {{ $emails->links('shared.layouts.pagination') }}
    </div>

</div>
