<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@section('scripts')
    <script src="{{ Vite::asset('resources/global/js/admin/customcanvas.js')  }}" type="module"></script>
    <script>
        document.getElementById("timeframe").addEventListener("change", function (e) {
            window.location.href = window.location.pathname + "?timeframe=" + e.target.value
        })
    </script>
@endsection
<div class="max-w-[85rem] mx-auto">
    @include('shared/alerts')
    <div class="flex flex-col">
        <div class="-m-1.5 overflow-x-auto">
            <div class="p-1.5 min-w-full inline-block align-middle">
                <div class="card">
                    <div class="flex justify-between">
                    <span class="flex items-center text-1xl uppercase font-medium text-gray-900 dark:text-white mb-4">
                        <span class="flex w-4 h-4 @if($vps->isRunning()) vps-running @elseif($vps->isStopped()) vps-stopped @else vps-warning @endif  rounded-full mr-1.5 flex-shrink-0"></span>
                        <p class="ml-2">
                            {{ __('proxmox::messages.graphs.title') }} - {{ $vps->hostname() }}
                        </p>
                    </span>
                        <div>
                            @include('shared/select', ['name' => 'timeframe', 'options' => $timeframes, 'value' => $vps->timeframe, 'label' => NULL])

                        </div>
                </div>
                    <div class="grid">
                        <div class="">
                            <div class="card-heading">
                                <h3 class="text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">{{ __('proxmox::messages.graphs.cpu') }}</h3>
                            </div>
                            <div class="chart-responsive">
                                <canvas height="140" is="custom-canvas" data-labels='{!! $vps->rdddata('time') !!}' data-backgrounds='["#00a65a"]' data-set='{!! $vps->rdddata('cpu') !!}' data-type="line" data-suffix="%" title="{{ __('proxmox::messages.graphs.cpu') }}"></canvas>
                            </div>
                        </div>
                        <div class="">
                            <div class="card-heading">
                                <h3 class="text-xs font-semibold mt-3 uppercase text-gray-600 dark:text-gray-400">{{ __('proxmox::messages.graphs.memory') }}</h3>
                            </div>
                            <div class="chart-responsive">
                                <canvas height="140" is="custom-canvas" data-labels='{!! $vps->rdddata('time') !!}' data-backgrounds='["#384454"]' data-set='{!! $vps->rdddata('mem') !!}' data-type="line" data-suffix="MB" title="{{ __('proxmox::messages.graphs.memory') }}"></canvas>
                            </div>
                        </div>

                        <div class="">
                            <div class="card-heading">
                                <h3 class="text-xs font-semibold mt-3 uppercase text-gray-600 dark:text-gray-400">{{ __('proxmox::messages.graphs.disk') }}</h3>
                            </div>
                            <div class="chart-responsive">
                                <canvas height="140" is="custom-canvas" data-labels='{!! $vps->rdddata('time') !!}' data-backgrounds='["#317AC1"]' data-set='{!! $vps->rdddata('disk') !!}' data-type="line" data-suffix="MB" title="{{ __('proxmox::messages.graphs.disk') }}"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
