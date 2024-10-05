<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@section('scripts')
    @parent
    @if ($vps->isRunning())
<script>
document.addEventListener("service_reloaded", function (e) {
    init()
})
function init() {
    let uptime = document.getElementById("uptime")
    if (uptime == null) {
        return;
    }
    let initialValues = uptime.innerHTML.trim().split(':')
    let days = parseInt(initialValues[0]) - 1
    let hours = parseInt(initialValues[1]) - 1
    let minutes = parseInt(initialValues[2]) - 1
    let seconds = parseInt(initialValues[3]) - 1
    let ms = 0
    let last = null;
    let t = setInterval(update, 100);
    function update() {
        ms++;
        if (ms === 10) {
            ms = 1;
            seconds += 1;
        }
        if (seconds === 60) {
            seconds = 0;
            minutes += 1;
        }
        if (minutes === 60) {
            minutes = 0;
            hours += 1;
        }
        if (hours === 24) {
            days += 1;
        }
        if (last != null) {
            if (last.days !== days || last.seconds !== seconds || last.hours !== hours || last.minutes !== minutes) {
                let parts = []
                if (days !== 0) {
                    parts.push(" " + days + " days ")
                }
                if (hours !== 0) {
                    parts.push(" " + hours + ":")
                }
                if (minutes !== 0) {
                    parts.push(minutes.toString().padStart(2, "0") + ":")
                }
                if (seconds !== 0) {
                    parts.push(seconds.toString().padStart(2, "0"))
                }
                uptime.innerHTML = " " + parts.join("")
            }
        }
        last = {days, seconds, minutes, hours}
    }
}
init()
</script>
@endif
@endsection

<span class="flex items-center text-1xl uppercase font-medium text-gray-900 dark:text-white mb-4">
    <span class="flex w-4 h-4 @if($vps->isRunning()) vps-running @elseif($vps->isStopped()) vps-stopped @else vps-warning @endif  rounded-full mr-1.5 flex-shrink-0"></span>
    <p class="ml-2">
        {{ __('provisioning.states.' . $vps->status()) }}
    </p>
</span>
<div class="grid sm:grid-cols-2 lg:grid-cols-2 gap-2 sm:gap-6">
    <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-slate-900 dark:border-gray-800">
        <div class="p-4 md:p-5 flex justify-between gap-x-3">
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-500">
                    {{ __('provisioning.memory') }}
                </p>
                <div class="mt-1 flex items-center gap-x-2">
                    <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-gray-200">
                        {{ (format_bytes($resources['maxmem'])) }}
                    </h3>
                    @if ($resources['mem'] != 0)

                    <p class="font-normal {{ $vps->colorSize($resources['mem'], $resources['maxmem']) }}">
                        {{ format_bytes($resources['mem']) }} {{ __('provisioning.used') }}
                    </p>
                        @endif
                </div>
            </div>
            <div class="flex-shrink-0 flex justify-center items-center w-[46px] h-[46px] bg-indigo-600 text-white rounded-full dark:bg-indigo-900 dark:text-indigo-200">
                <i class="bi bi-memory"></i>
            </div>
        </div>
    </div>
    <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-slate-900 dark:border-gray-800">
        <div class="p-4 md:p-5 flex justify-between gap-x-3">
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-500">
                    {{ __('provisioning.disk') }}
                </p>
                <div class="mt-1 flex items-center gap-x-2">

                    <h3 class="mt-1 text-xl font-medium text-gray-800 dark:text-gray-200">
                        {{ (format_bytes($resources['maxdisk'])) }}
                    </h3>
                    @if ($resources['disk'] != 0)
                        <p class="font-normal {{ $vps->colorSize($resources['disk'], $resources['maxdisk']) }}">
                            {{ format_bytes($resources['disk']) }} {{ __('provisioning.used') }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="flex-shrink-0 flex justify-center items-center w-[46px] h-[46px] bg-indigo-600 text-white rounded-full dark:bg-indigo-900 dark:text-indigo-200">
                <i class="bi bi-hdd"></i>
            </div>
        </div>
    </div>
    <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-slate-900 dark:border-gray-800">
        <div class="p-4 md:p-5 flex justify-between gap-x-3">
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-500">
                    {{ __('provisioning.cores') }}
                </p>
                <div class="mt-1 flex items-center gap-x-2">
                    <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-gray-200">
                        {{ $vps->getCores()  }}
                    </h3>
                    @if (($resources['cpu'] * 100) > 0)
                        <p class="font-normal {{ $vps->colorSize($resources['cpu'], $resources['cpus']) }}">
                            {{ number_format($resources['cpu'] * 100, 2) }}% {{ __('provisioning.used') }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="flex-shrink-0 flex justify-center items-center w-[46px] h-[46px] bg-indigo-600 text-white rounded-full dark:bg-indigo-900 dark:text-indigo-200">
                <i class="bi bi-cpu"></i>
            </div>
        </div>
    </div>
    <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-slate-900 dark:border-gray-800">
        <div class="p-4 md:p-5 flex justify-between gap-x-3">
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-500">
                    {{ __('provisioning.rate') }}
                </p>
                <div class="mt-1 flex items-center gap-x-2">
                    <h3 class="mt-1 text-xl font-medium text-gray-800 dark:text-gray-200">
                        {{ $vps->getBandwidth()  }}
                    </h3>
                </div>
            </div>
            <div class="flex-shrink-0 flex justify-center items-center w-[46px] h-[46px] bg-indigo-600 text-white rounded-full dark:bg-indigo-900 dark:text-indigo-200">
                <i class="bi bi-hdd-rack"></i>

            </div>
        </div>
    </div>
</div>
            <div class="mt-2">
                <div class="flex mt-2">
                    @if ($vps->isStopped())
                        <form method="POST" action="{{ route('proxmox.power', ['service' => $service, 'power' => 'start']) }}" class="w-full">
                            @csrf
                            <button class="w-full mr-2 py-2 px-4 mt-4 btn-primary text-center py-2 px-4">
                                {{ __('provisioning.start') }}
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('proxmox.power', ['service' => $service, 'power' => 'stop']) }}" class="w-full">
                            @csrf
                            <button class="w-full mr-2 btn-secondary text-center py-2 px-4">
                                {{ __('provisioning.stop') }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('proxmox.power', ['service' => $service, 'power' => 'reboot']) }}" class="w-full">
                            @csrf
                            <button class="w-full ml-2 btn-danger text-center py-2 px-4">
                                {{ __('provisioning.restart') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="w-full md:w-2/3 pr-2 mb-4 mt-4">
                <div class="card">
                    <h5 class="mb-2 text-lg font-bold tracking-tight text-gray-900 dark:text-white">{{ __('proxmox::messages.panel.info') }}</h5>
                    @if ($vps->isRunning())
                        <p class="mb-3 font-normal text-gray-700 dark:text-gray-400"><b class="tracking-wide text-gray-500 dark:text-gray-400"><i class="bi bi-alarm-fill ml-2"></i> {{ __('proxmox::messages.uptime') }}</b> :  <span id="uptime">{{ $vps->getUptime() }}</span></p>
                    @endif
                    @if ($vps->getOS())
                    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400"><b class="tracking-wide text-gray-500 dark:text-gray-400"><i class="bi bi-usb-plug-fill ml-2"></i> {{ __('proxmox::messages.data.os') }}</b> : {{ $vps->getOS() }}</p>
                    @endif
                    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400"><b class="tracking-wide text-gray-500 dark:text-gray-400"><i class="bi bi-person-badge-fill ml-2"></i> {{ __('proxmox::messages.username') }}</b> : root</p>
                    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400"><b class="tracking-wide text-gray-500 dark:text-gray-400"><i class="bi bi-broadcast-pin ml-2"></i> {{ __('proxmox::messages.ipam.ip') }}</b> : {{ $vps->getPrimaryIp()->ip }}</p>
                    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400"><b class="tracking-wide text-gray-500 dark:text-gray-400"><i class="bi bi-bookmarks-fill ml-2"></i> {{ __('proxmox::messages.hostname') }}</b> : {{ $vps->hostname() }}</p>
                </div>
            </div>

