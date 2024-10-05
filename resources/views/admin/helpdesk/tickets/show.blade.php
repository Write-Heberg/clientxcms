<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin/layouts/admin')
@section('title',  __($ticket->subject, ['name' => $item->username]))
@section('style')
    <link rel="stylesheet" href="{{ Vite::asset('resources/global/css/simplemde.min.css') }}">
@endsection
@section('scripts')
    <script src="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js"></script>
    <script src="{{ Vite::asset('resources/global/js/mdeditor.js') }}"></script>
@endsection
@section('content')
    <div class="max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
        @include('shared/alerts')
        <div class="flex flex-col">
            <div class="-m-1.5 overflow-x-auto">
                <div class="p-1.5 min-w-full inline-block align-middle">

                    <div class="grid lg:grid-cols-12 md:grid-cols-4">
                        <div class="lg:col-span-8 col-span-4">
                            <div class="card">
                                <div class="card-heading">
                                    <div>

                                        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                                            <x-badge-state state="{{ $ticket->status }}"></x-badge-state>

                                            {{ $ticket->subject }}
                                        </h2>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('client.support.show.index_description') }}
                                        </p>
                                    </div>
                                </div>
                                <ul class="space-y-5">
                                    @foreach ($ticket->messages as $i => $message)

                                        <!-- End Chat -->
                                        <!-- Chat -->
                                        <li class="{{ $message->containerClasses('admin') }}">
                                            <!-- Card -->
                                            <div class="bg-white border border-gray-200 rounded-2xl p-4 space-y-3 dark:bg-neutral-900 dark:border-neutral-700">

                                                <h2 class="font-medium text-gray-800 dark:text-white">
                                                    <div class="hs-tooltip inline-block">
                                    <span class="hs-tooltip-toggle mr-2 relative inline-flex items-center justify-center h-[2.375rem] w-[2.375rem] rounded-full bg-gray-500 font-semibold text-white leading-none">
    {{ $message->initials() }}
</span>
                                                        <span class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 inline-block absolute invisible z-20 py-1.5 px-2.5 bg-gray-900 text-xs text-white rounded-lg dark:bg-neutral-700" role="tooltip">
      {{ $message->isCustomer() ? $message->customer->fullName : $message->staffUsername() }}
    </span>
                                                    </div>
                                                    {{ $message->replyText($i, 'admin') }}
                                                </h2>
                                                <div class="space-y-1.5">
                                                    <p>
                                                        {!! $message->formattedMessage() !!}
                                                    </p>
                                                </div>
                                                <p class="text-sm text-gray-700 mt-2 flex justify-between">
                                                    <span>{{ $message->created_at->format('d/m/y H:i') }} @if ($message->isStaff())- {{ $message->staffUsername() }} @endif</span>
                                                    @if ($message->hasAttachments())
                                                        <span>
                                                            @foreach ($message->getAttachments() as $attachment)
                                                                <a href="{{ route('admin.helpdesk.tickets.download', ['ticket' => $ticket, 'attachment' => $attachment]) }}" class="text-blue-600 hover:underline dark:text-blue-500 dark:hover:text-blue-400">
                                                                    <i class="bi bi-file-earmark"></i>
                                                                    {{ Str::limit($attachment->filename, 30) }}
                                                                </a>
                                                            @endforeach
                                                        </span>
                                                    @endif
                                                </p>

                                            </div>

                                        </li>

                                        @if ($message->hasAttachments())
                                            <li>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 text-center ">
                                                    <i class="bi bi-file-earmark-plus-fill"></i> {{ __('client.support.show.attachments') }}
                                                </p>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                                @if ($ticket->isOpen())
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mt-6">
                                        {{ __('client.support.show.replyinticket') }}
                                    </h3>
                                    <form method="POST" action="{{ route('admin.helpdesk.tickets.reply', ['ticket' => $ticket]) }}" enctype="multipart/form-data">
                                        @csrf

                                        <textarea id="editor" name="content">{{ old('content') }}</textarea>

                                    @if ($errors->has('content'))
                                                @foreach ($errors->get('content') as $error)
                                                    <div class="text-red-500 text-sm mt-2">
                                                        {{ $error }}
                                                    </div>
                                                @endforeach
                                            @endif

                                            <div class="col-span-2 mt-2">
                                                @include('shared/file2', ['name' => 'attachments', 'label' => __('client.support.attachments'), 'help' => __('client.support.attachments_help', ['size' => setting('helpdesk_attachments_max_size'), 'types' => formatted_extension_list(setting('helpdesk_attachments_allowed_types'))])])
                                            </div>
                                        <button class="btn btn-primary mt-2">{{ __('client.support.show.reply') }}</button>
                                        <button class="btn btn-secondary mt-2" name="close">{{ __('client.support.show.replyandclose') }}</button>

                                    </form>
                                @else

                                    <div class="alert text-yellow-800 bg-yellow-100 mt-2" role="alert">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                        {{ __('client.support.show.closed2') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="lg:col-span-4 col-span-4">
                            <div class="card ml-2">
                                <div class="flex -space-x-2 mb-2">
                                    @foreach ($ticket->attachedUsers() as $initials => $username)
                                        <div class="hs-tooltip inline-block">
                                    <span class="hs-tooltip-toggle relative inline-flex items-center justify-center h-[2.375rem] w-[2.375rem] rounded-full bg-gray-500 font-semibold text-white leading-none">
  {{ $initials }}
</span>
                                            <span class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 inline-block absolute invisible z-20 py-1.5 px-2.5 bg-gray-900 text-xs text-white rounded-lg dark:bg-neutral-700" role="tooltip">
      {{ $username }}
    </span>
                                        </div>
                                    @endforeach
                                </div>
                                <ul class="max-w-lg flex flex-col">
                                    <li class="inline-flex items-center gap-x-3.5 py-3 px-4 text-sm font-medium bg-white border border-gray-200 text-gray-800 -mt-px first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                                        <i class="bi bi-buildings"></i>
                                        {{ $ticket->department->name }}
                                    </li>
                                    @if ($ticket->isValidRelated())

                                        <a href="{{ $ticket->related->relatedLink() }}" class="inline-flex items-center gap-x-3.5 py-3 px-4 text-sm font-medium bg-white border border-gray-200 text-gray-800 -mt-px first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                                            <i class="bi bi-box"></i>
                                            {{ $ticket->related->relatedName() }}
                                                <i class="bi bi-box-arrow-up-right"></i>
                                        </a>

                                    @endif
                                    <a href="{{ route('admin.customers.show', $ticket->customer) }}" class="inline-flex items-center gap-x-3.5 py-3 px-4 text-sm font-medium bg-white border border-gray-200 text-gray-800 -mt-px first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                                        <i class="bi bi-person"></i>
                                        {{ $ticket->customer->fullName }}
                                        <i class="bi bi-box-arrow-up-right"></i>

                                    </a>
                                    <li class="inline-flex items-center gap-x-3.5 py-3 px-4 text-sm font-medium bg-white border border-gray-200 text-gray-800 -mt-px first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                                        <i class="bi bi-send-dash"></i>
                                        {{ __('client.support.priority') }} {{ strtolower($ticket->priorityLabel()) }}
                                    </li>
                                    <li class="inline-flex items-center gap-x-3.5 py-3 px-4 text-sm font-medium bg-white border border-gray-200 text-gray-800 -mt-px first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                                        <i class="bi bi-calendar-date"></i>
                                        {{ __('client.support.show.open_on', ['date' => $ticket->created_at->format('d/m H:i')]) }}
                                    </li>

                                    @if ($ticket->closed_at)
                                        <li class="inline-flex items-center gap-x-3.5 py-3 px-4 text-sm font-medium bg-white border border-gray-200 text-gray-800 -mt-px first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                                            <i class="bi bi-x-square"></i>
                                            {{ __('client.support.show.closed_on', ['date' => $ticket->closed_at->format('d/m H:i')]) }}
                                        </li>
                                    @endif
                                </ul>

                                <ul class="flex flex-col justify-end text-start -space-y-px mt-3">
                                    @foreach ($ticket->attachments as $attachment)
                                        <li class="flex items-center gap-x-2 p-3 text-sm bg-white border text-gray-800 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200">
                                            <div class="w-full flex justify-between truncate">
      <span class="me-3 flex-1 w-0 truncate">
         {{ Str::limit($attachment->filename, 30) }}
      </span>
                                                <a href="{{ route('admin.helpdesk.tickets.download', ['ticket' => $ticket, 'attachment' => $attachment]) }}" class="flex items-center gap-x-2 text-gray-500 hover:text-blue-600 whitespace-nowrap dark:text-neutral-500 dark:hover:text-blue-500">
                                                    <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                        <polyline points="7 10 12 15 17 10"></polyline>
                                                        <line x1="12" x2="12" y1="15" y2="3"></line>
                                                    </svg>
                                                </a>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                                <button class="btn btn-secondary mt-2 w-full" data-hs-overlay="#edit-overlay">{{ __('admin.helpdesk.tickets.edit') }}</button>
                            @if ($ticket->isOpen())
                                    <form method="POST" action="{{ route('admin.helpdesk.tickets.close', ['ticket' => $ticket]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger mt-2 w-full" type="submit">{{ __('client.support.show.close') }}</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.helpdesk.tickets.reopen', ['ticket' => $ticket]) }}">
                                        @csrf
                                        <button class="btn btn-primary mt-2 w-full" type="submit">{{ __('client.support.show.reopen') }}</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div id="edit-overlay" class="overflow-x-hidden overflow-y-auto hs-overlay hs-overlay-open:translate-x-0 translate-x-full fixed top-0 end-0 transition-all duration-300 transform h-full max-w-lg w-full w-full z-[80] bg-white border-s dark:bg-gray-800 dark:border-gray-700 hidden" tabindex="-1">
        <div class="flex justify-between items-center py-3 px-4 border-b dark:border-gray-700">
            <h3 class="font-bold text-gray-800 dark:text-white">
                {{ __($translatePrefix . '.edit') }}
            </h3>
            <button type="button" class="flex justify-center items-center w-7 h-7 text-sm font-semibold rounded-full border border-transparent text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:text-white dark:hover:bg-gray-700 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600" data-hs-overlay="#metadata-overlay">
                <span class="sr-only">{{ __('global.closemodal') }}</span>
                <svg class="flex-shrink-0 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <div class="p-4">
            <form method="POST" action="{{ route($routePath . '.update', ['ticket' => $ticket]) }}">
                @csrf
                @method('PUT')
                <div>
                    @include("shared/input", ["name" => "subject", "label" => __("client.support.subject"), 'value' => old('subject', $ticket->subject)])
                </div>
                <div>
                    @include("shared/select", ["name" => "priority", "label" => __("client.support.priority"), "options" => $priorities, 'value' => old('priority', $ticket->priority)])
                </div>
                <div>
                    @include("shared/select", ["name" => "related_id", "label" => __("client.support.create.relatedto"), "options" => $related, 'value' => old('related_id', $ticket->relatedValue())])
                </div>
                <div>
                    @include("shared/select", ["name" => "department_id", "label" => __("client.support.department"), "options" => $departments, 'value' => old('department_id', $item->department_id)])
                </div>
                <button class="btn btn-primary mt-2">{{ trans("global.save")  }}</button>
            </form>
        </div>
    </div>
@endsection
