<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
<div class="grid md:grid-cols-2 gap-4 grid-cols-1">
<div>
        @include('admin/shared/input', ['name' => 'paypal_email', 'label' => __('admin.settings.store.gateways.fields.paypal_email'), 'value' => env('PAYPAL_EMAIL')])
    </div>
    <div>
        @include('admin/shared/select', ['name' => 'sandbox', 'label' => __('admin.settings.store.gateways.fields.sandbox'), 'value' => env('PAYPAL_SANDBOX', 'true') ? 'sandbox' : 'live', 'options' => ['sandbox' => __('global.enabled'), 'live' => __('global.disabled')]])
    </div>
</div>
