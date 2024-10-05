<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('install.layout')
@section('title', __('install.settings.title'))
@section('content')
    <form method="POST" action="{{ route('install.settings') }}">
        @csrf
    @include('shared.alerts')
        <div class="mt-2">
            @include('shared.input', ['name' => 'app_name', 'value' => env('APP_NAME'), 'label' => __('install.settings.hosting_name')])
        </div>
        <div class="mt-2">
            @include('shared.input', ['name' => 'client_id', 'value' =>  old('client_id'), 'label' => __('install.settings.client_id')])
        </div>
        <div class="mt-2">
            @include('shared.input', ['name' => 'client_secret', 'value' => old('client_id'), 'label' => __('install.settings.client_secret')])
        </div>
        <button type="submit" class="mt-4 btn btn-primary w-full" {{ !$isMigrated ? 'disabled' : '' }}>
            {{ __('install.settings.connect') }}
        </button>
    </form>
    @endsection
