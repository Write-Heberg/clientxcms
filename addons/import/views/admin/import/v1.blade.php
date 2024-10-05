<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin/layouts/admin')
@section('title', __('import::import.title'))
@section('content')
    <div class="max-w-[85rem] py-5 lg:py-7 mx-auto">
        @include('admin/shared/alerts')
        <div class="flex flex-col">
            <div class="-m-1.5 overflow-x-auto">
                <div class="p-1.5 min-w-full inline-block align-middle">

                    <div class="card">

                        <h4 class="font-semibold uppercase text-gray-600 dark:text-gray-400">

                        {{ __('import::import.title') }}
                        </h4>
                        <p class="mb-2 font-semibold text-gray-600 dark:text-gray-400">
                        {{ __('import::import.description') }}</p>
                        @if ($output == null)

                        <form method="POST" action="{{ route('admin.import.v1') }}">
                            <div class="grid grid-cols-3 gap-3">
                                @foreach (collect(__('import::import.v1.importables'))->chunk(3) as $row)
                                    <ul class="space-y-3 text-sm">

                                        @foreach($row as $k => $importable)
                                            <li class="flex space-x-3">
                                    <span class="text-gray-800 dark:text-gray-400">
                                        <div class="flex">
  <input type="checkbox" name="importables[]" value="{{ $k }}" class="shrink-0 mt-0.5 border-gray-200 rounded text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800" id="checkbox-{{ $k }}" checked="">
  <label for="checkbox-{{ $k }}" class="text-sm text-gray-500 ms-3 dark:text-neutral-400">{{ $importable }}</label>


</div>
    </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endforeach
                            </div>
                            @csrf
                            @include('admin/shared/input', ['name' => 'host', 'label' => __('import::import.db.host')])
                            @include('admin/shared/input', ['name' => 'port', 'label' => __('import::import.db.port'), 'value' => "3306"])
                            @include('admin/shared/input', ['name' => 'database', 'label' => __('import::import.db.database')])
                            @include('admin/shared/input', ['name' => 'username', 'label' => __('import::import.db.username')])
                            @include('admin/shared/password', ['name' => 'password', 'label' => __('import::import.db.password')])
                            @include('admin/shared/checkbox', ['name' => 'truncate', 'label' => __('import::import.truncate'), 'value' => true])
                            <button class="btn-primary mt-3" type="submit">{{ __('import::import.import') }}</button>
                            <p class="text-primary">{{ __('import::import.delay') }}</p>
                        </form>
                        @else
                            <code class="overflow-y-auto" style="max-height: 500px;">
                                {!!  nl2br($output) !!}
                            </code>
                            <form method="POST" action="{{ route('admin.report') }}">
                                @csrf
                                <button class="btn-primary mt-3" type="submit">{{ __('import::import.downloadreport') }}</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
