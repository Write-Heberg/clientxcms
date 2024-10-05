<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin.settings.sidebar')
@section('title', $gateway->name)
@section('setting')
    <div class="card">
        <h4 class="font-semibold uppercase text-gray-600 dark:text-gray-400">
            {{$gateway->name }}
        </h4>
        <p class="mb-2 font-semibold text-gray-600 dark:text-gray-400">
            {{ __('admin.settings.store.gateways.description', ['name' => $gateway->name]) }}
        </p>

        <form action="{{ route('admin.settings.store.gateways.save', compact('gateway')) }}" method="POST" enctype="multipart/form-data">
            @csrf
                @include('shared.input', ['name' => 'name', 'label' => __('admin.settings.store.gateways.fields.name'), 'value' => $gateway->name])
                {!! $config !!}
                @method('PUT')
            @include('shared/status-select', ['value' => $gateway->status])
            <button type="submit" class="btn btn-primary mt-2">{{ __('global.save') }}</button>
        </form>
@endsection
