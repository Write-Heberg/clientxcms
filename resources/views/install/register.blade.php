<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('install.layout')
@section('title', __('install.register.title'))
@section('content')
    <form method="POST" action="{{ route('install.register') }}">
        @csrf
    @include('shared.alerts')

        <div class="mt-2">
            @include('shared.input', ['name' => 'firstname', 'type' => 'text', 'label' => __('global.firstname')])
        </div>

        <div class="mt-2">
            @include('shared.input', ['name' => 'lastname', 'type' => 'text', 'label' => __('global.lastname')])
        </div>

        <div class="mt-2">
            @include('shared.input', ['name' => 'email', 'type' => 'email', 'label' => __('global.email')])
        </div>

        <div class="mt-2">
            @include('shared.password', ['name' => 'password', 'type' => 'password', 'label' => __('global.password')])
        </div>

        <div class="mt-2">
            @include('shared.password', ['name' => 'password_confirmation', 'label' => __('global.password_confirmation')])
        </div>
        <button type="submit" class="mt-4 btn btn-primary w-full">
            {{ __('install.register.btn') }}
        </button>
    </form>
    @endsection
