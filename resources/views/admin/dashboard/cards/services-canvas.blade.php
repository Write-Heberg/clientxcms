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
@endsection
<div class="flex flex-col">
    <div class="card-heading">
        <h3 class="text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">{{ __('admin.dashboard.widgets.services_canvas') }}</h3>
    </div>
    <div class="-m-1.5 overflow-x-auto">
        <div class="p-1.5 min-w-full inline-block align-middle">

                @if ($dto->isEmpty())
            <p>{{ __("global.no_results") }}
               @else
            <div class="chart-responsive">
                <canvas height="140" is="custom-canvas" data-labels="{{ $dto->getLabels() }}" data-backgrounds="{{ $dto->getColors() }}" data-set="{{ $dto->getValues() }}" ></canvas>
            </div>
                @endif
        </div>
    </div>
</div>

