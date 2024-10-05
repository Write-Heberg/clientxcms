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
        <h3 class="text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">{{ __('admin.dashboard.widgets.customer_search') }}</h3>
    </div>
    <div class="-m-1.5 overflow-x-auto">
        <form method="GET" action="{{ route('admin.customers.index') }}" class="h-full">
            @include('shared/input', ['name' => 'q', 'label' => __('global.lookup')])

            @include('shared/select', ['name' => 'field', 'options' => $fields,'value' => 'email', 'label' => __('global.searchfrom')])
            <button class="btn btn-primary flex mt-5 w-full" type="submit">{{ __('global.search') }}</button>

        </form>
    </div>
</div>

