<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>

<h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
    {{ __('admin.services.create.choose') }}
</h2>
@include('admin/shared/select', ['name' => 'vmid', 'label' => __('proxmox::messages.templates.vmids'), 'options' => $vps, 'value' => old('vmid')])
<h4 class="text-gray-800 dark:text-gray-500">
   {{ __('proxmox::messages.importinfo') }}
</h4>
<div class="grid grid-cols-4 md:grid-cols-4 gap-4">
    <div>
        @include("admin/shared/input", ['name' => 'memory', 'label' => __('provisioning.memory'), 'value' => $config->memory, 'help' => __('provisioning.in_gb'), 'type' => 'number','step' => '0.1', 'min' => 0])
    </div>
    <div>
        @include("admin/shared/input", ['name' => 'disk', 'label' => __('provisioning.disk'), 'value' => $config->disk, 'help' => __('provisioning.in_gb'), 'type' => 'number', 'step' => '0.1', 'min' => 0])
    </div>

    <div>
        @include("admin/shared/input", ['name' => 'cores', 'label' => __('provisioning.cores'), 'value' => $config->cores, 'type' => 'number'])
    </div>

    <div>
        @include("admin/shared/input", ['name' => 'sockets', 'label' => __('provisioning.sockets'), 'value' => $config->sockets, 'type' => 'number'])
    </div>
    <div>
        @include("admin/shared/search-select-multiple", ['name' => 'oses[]', 'label' => __('proxmox::messages.oses.title'), 'options' => $oses, 'value' => [], 'multiple' => true])
    </div>
    <div>
        @include('admin/shared/select', ['name' => 'storage', 'label' => __('provisioning.disk'), 'value' => $config->storage, 'options' => $storages])
    </div>

    <div>
        @include("admin/shared/search-select-multiple", ['name' => 'templates[]', 'label' => __('proxmox::messages.templates.title'), 'options' => $templates, 'value' => [], 'multiple' => true])
    </div>

    <div>
        @include("admin/shared/select", ['name' => 'rate', 'label' => __('provisioning.rate'), 'value' => $config->rate, 'options' => $rates])
    </div>
</div>
<div class="grid grid-cols-3 md:grid-cols-3 gap-4">

<div>
        @include("admin/shared/input", ['name' => 'max_reinstall', 'label' => __('proxmox::messages.max_reinstall'), 'value' => $config->max_reinstall, 'type' => 'number', 'max' => 100, 'min'=> -1, 'step' => 1])
    </div>
    <div>
        @include("admin/shared/input", ['name' => 'max_snapshots', 'label' => __('proxmox::messages.max_snapshots'), 'value' => $config->max_snapshots, 'type' => 'number', 'max' => 100, 'min'=> -1, 'step' => 1])
    </div>

    <div>
        @include("admin/shared/input", ['name' => 'max_backups', 'label' => __('proxmox::messages.max_backups'), 'value' => $config->max_backups, 'type' => 'number', 'max' => 100, 'min'=> -1, 'step' => 1])
    </div>
</div>
<p class="text-sm text-gray-500 mt-2">{{ __('proxmox::messages.max_helpers') }}</p>
