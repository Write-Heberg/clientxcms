<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
<div class="grid grid-cols-3 gap-4">
    <div>
        @include("admin/shared/input", ['name' => 'memory', 'label' => __('provisioning.memory'), 'value' => $config->memory, 'help' => __('provisioning.in_gb'), 'type' => 'number','step' => '0.1', 'min' => 0])
    </div>
    <div>
        @include("admin/shared/input", ['name' => 'disk', 'label' => __('provisioning.disk'), 'value' => $config->disk, 'help' => __('provisioning.in_gb'), 'type' => 'number', 'step' => '0.1', 'min' => 0])
    </div>
    <div>
        @include("admin/shared/select", ['name' => 'type', 'label' => __('proxmox::messages.virtualisation_type'), 'options' => $types, 'value' => $config->type])
    </div>
    <div class="col-span-2">
        @include("admin/shared/search-select-multiple", ['name' => 'oses[]', 'label' => __('proxmox::messages.oses.title'), 'options' => $oses, 'value' => $currentOses, 'multiple' => true])
    </div>
    <div>
        @include('admin/shared/select', ['name' => 'storage', 'label' => __('provisioning.disk'), 'value' => $config->storage, 'options' => $storages])
    </div>

    <div class="col-span-2">
        @include("admin/shared/search-select-multiple", ['name' => 'templates[]', 'label' => __('proxmox::messages.templates.title'), 'options' => $templates, 'value' => $currentTemplates, 'multiple' => true])
    </div>

    <div>
        @include('admin/shared/select', ['name' => 'server_id', 'label' => __('proxmox::messages.server'), 'options' => $servers, 'value' => $config->server_id])
    </div>

    <div class="col-span-2">
        @include('admin/shared/select', ['name' => 'node', 'label' => __('proxmox::messages.node'), 'options' => $nodes, 'value' => $config->node])
        @include('admin/shared/select', ['name' => 'disk_storage', 'label' => __('proxmox::messages.disk_storage'), 'value' => $config->disk_storage, 'options' => $storages])
    </div>

    <div>
        @include("admin/shared/select", ['name' => 'rate', 'label' => __('provisioning.rate'), 'value' => $config->rate, 'options' => $rates])
    </div>
    <div>
        @include("admin/shared/input", ['name' => 'cores', 'label' => __('provisioning.cores'), 'value' => $config->cores, 'type' => 'number'])
    </div>

    <div>
        @include("admin/shared/input", ['name' => 'sockets', 'label' => __('provisioning.sockets'), 'value' => $config->sockets, 'type' => 'number'])
    </div>

    <div>
        @include("admin/shared/select", ['name' => 'bridge', 'label' => __('proxmox::messages.ipam.bridge'), 'value' => $config->bridge, 'options' => $bridges])
    </div>
    <div class="col-span-3">
        @include("admin/shared/textarea", ['name' => 'features', 'label' => __('proxmox::messages.features'), 'value' => $config->features])
    </div>

    <div>
        @include("admin/shared/input", ['name' => 'max_reinstall', 'label' => __('proxmox::messages.max_reinstall'), 'value' => $config->max_reinstall, 'type' => 'number', 'max' => 100, 'min'=> -1, 'step' => 1])
    </div>
    <div>
        @include("admin/shared/input", ['name' => 'max_backups', 'label' => __('proxmox::messages.max_backups'), 'value' => $config->max_backups, 'type' => 'number', 'max' => 100, 'min'=> -1, 'step' => 1])
    </div>
    <div>
        @include("admin/shared/input", ['name' => 'max_snapshots', 'label' => __('proxmox::messages.max_snapshots'), 'value' => $config->max_snapshots, 'type' => 'number', 'max' => 100, 'min'=> -1, 'step' => 1])
    </div>
    <p class="text-sm text-gray-500 mt-2 col-span-3">{{ __('proxmox::messages.max_helpers') }}</p>


    <div class="col-span-2">
        @include("shared/checkbox", ['name' => 'unprivileged', 'label' => __('proxmox::messages.unprivileged'), 'checked' => $config->unprivileged])
    </div>

</div>
<p class="text-sm text-gray-500 mt-2">{{ __('proxmox::messages.clear_cache_if_empty') }}</p>

