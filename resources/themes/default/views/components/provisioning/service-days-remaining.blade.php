<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@props(['expires_at' => null, 'state' => 'active', 'date_at' => null])
@php
    $days = $expires_at != null ? \Carbon\Carbon::parse($expires_at)->diffInDays() : null;
    $inFuture = $expires_at != null ? \Carbon\Carbon::parse($expires_at)->isFuture() : false;
    if ($inFuture == false) {
        $days = null;
    }
@endphp
@if ($days == null && $expires_at == null)
    <span class="py-1 px-1.5 inline-flex items-center gap-x-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full dark:bg-blue-500/10 dark:text-blue-500">
          <div class="hs-tooltip">
            <div class="hs-tooltip-toggle">
              <svg class="flex-shrink-0 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
              <span class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded shadow-sm dark:bg-slate-700" role="tooltip">
                  {{ __('client.services.expirationtooltipneverexpire')  }}
              </span>
            </div>
          </div>
          {{ __('client.services.onetime') }}
</span>
@endif
@if (gettype($days) == "NULL" && $expires_at != null)
    <span class="py-1 px-1.5 inline-flex items-center gap-x-1 text-xs font-medium bg-red-100 text-red-800 rounded-full dark:bg-red-500/10 dark:text-red-500">
          <div class="hs-tooltip">
            <div class="hs-tooltip-toggle">
              <svg class="flex-shrink-0 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
              <span class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded shadow-sm dark:bg-slate-700" role="tooltip">
                                      {{ __('client.services.expirationtooltipexpired', ['date' => \Carbon\Carbon::parse($date_at)->format('d/m/y')]) }}

              </span>
            </div>
          </div>
                            {{ __('global.states.expired') }}


</span>
@endif
@if ($days >= 7)
    <span class="py-1 px-2 inline-flex items-center gap-x-1 text-xs font-medium bg-teal-100 text-teal-800 rounded-full dark:bg-teal-500/10 dark:text-teal-500">
          <div class="hs-tooltip">
            <div class="hs-tooltip-toggle">
              <svg class="flex-shrink-0 w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
              <span class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded shadow-sm dark:bg-slate-700" role="tooltip">
    @if ($state == 'suspended')
                      {{ __('client.services.expirationtooltipsuspended', ['date' => \Carbon\Carbon::parse($date_at)->format('d/m/y')]) }}
                  @endif
                  @if ($state == 'expired' || $state == 'refunded')
                      {{ __('client.services.expirationtooltipexpired', ['date' => \Carbon\Carbon::parse($date_at)->format('d/m/y')]) }}
                  @endif
                  @if ($state == 'active' || $state == 'cancelled' || $state == 'pending')
                      {{ __('client.services.expirationtooltip', ['date' => \Carbon\Carbon::parse($expires_at)->format('d/m/y')]) }}
                  @endif
              </span>
            </div>
          </div>
                            {{ __('client.services.daysremaining', ['days' => $days]) }}

</span>
@endif

@if ($days <= 3 && gettype($days) == "integer")
    <span class="py-1 px-2 inline-flex items-center gap-x-1 text-xs font-medium bg-red-100 text-red-800 rounded-full dark:bg-red-500/10 dark:text-red-500">


          <div class="hs-tooltip">
            <div class="hs-tooltip-toggle">
              <svg class="flex-shrink-0 w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
              <span class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded shadow-sm dark:bg-slate-700" role="tooltip">
                  @if ($state == 'suspended')
                      {{ __('client.services.expirationtooltipsuspended', ['date' => \Carbon\Carbon::parse($date_at)->format('d/m/y')]) }}
                  @endif
                  @if ($state == 'expired' || $state == 'refunded')
                      {{ __('client.services.expirationtooltipexpired', ['date' => \Carbon\Carbon::parse($date_at)->format('d/m/y')]) }}
                  @endif
                  @if ($state == 'active' || $state == 'cancelled' || $state == 'pending')
                      {{ __('client.services.expirationtooltip', ['date' => \Carbon\Carbon::parse($expires_at)->format('d/m/y')]) }}
                  @endif
              </span>
            </div>
          </div>
        @if ($days < 1)
            {{ __('client.services.dayremaining', ['days' => $days]) }}
        @else
            {{ __('client.services.daysremaining', ['days' => $days]) }}
        @endif
</span>
@endif
@if ($days < 7 && $days > 3)


    <span class="py-1 px-2 inline-flex items-center gap-x-1 text-xs bg-gray-100 text-gray-800 rounded-full dark:bg-slate-500/20 dark:text-slate-400">

    <div class="hs-tooltip">
        <div class="hs-tooltip-toggle">
            <svg class="flex-shrink-0 w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
            <span class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded shadow-sm dark:bg-slate-700" role="tooltip">
          @if ($state == 'suspended')
                    {{ __('client.services.expirationtooltipsuspended', ['date' => \Carbon\Carbon::parse($date_at)->format('d/m/y')]) }}
                @endif
                @if ($state == 'expired' || $state == 'refunded')
                    {{ __('client.services.expirationtooltipexpired', ['date' => \Carbon\Carbon::parse($date_at)->format('d/m/y')]) }}
                @endif
                @if ($state == 'active' || $state == 'cancelled' || $state == 'pending')
                    {{ __('client.services.expirationtooltip', ['date' => \Carbon\Carbon::parse($expires_at)->format('d/m/y')]) }}
                @endif
              </span>
        </div>
    </div>
                    {{ __('client.services.daysremaining', ['days' => $days]) }}

</span>
@endif
