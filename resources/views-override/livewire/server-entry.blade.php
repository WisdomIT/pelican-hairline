{{-- Hairline 테마의 서버 카드 오버라이드 (#61).
     원본: resources/views/livewire/server-entry.blade.php (Pelican 코어)
     ⚠ 코어 뷰를 통째로 대체한다 — 패널 업그레이드 때 원본과 대조할 것.
     바꾼 것: ① 좌측 상태 세로바 제거 → 글자 태그(배지)로 ② 중첩 패딩·마진·갭 축소 --}}
@php
    $actiongroup = \App\Filament\App\Resources\Servers\Pages\ListServers::getPowerActionGroup()->record($server);
    $backgroundImage = $server->icon ?? $server->egg->icon;

    $serverEntryColumn = $column ?? \App\Filament\Components\Tables\Columns\ServerEntryColumn::make('server_entry');
    $serverNodeStatistics = $server->node->statistics();
    $serverNodeSystemInfo = $server->node->systemInformation();

    $warningPercent = $serverEntryColumn->getWarningThresholdPercent() ?? 0.7;
    $dangerPercent = $serverEntryColumn->getDangerThresholdPercent() ?? 0.9;
@endphp
<div wire:poll.15s
     class="relative cursor-pointer"
     x-on:click="{{ $component->redirectUrl() }}"
     x-on:auxclick.prevent="if ($event.button === 1) {{ $component->redirectUrl(true) }}">

    <div class="flex-1 dark:bg-gray-800 dark:text-white rounded-lg overflow-hidden hl-card-pad">
        @if($backgroundImage)
            <div style="
                position: absolute;
                inset: 0;
                background: url('{{ $backgroundImage }}') right no-repeat;
                background-size: contain;
                opacity: 0.20;
                max-width: 680px;
                max-height: 140px;
            "></div>
        @endif

        <div @class([
            'flex items-center gap-2 hl-card-head',
            'hl-card-head-solo' => !$server->description,
        ])>
            {{-- 상태를 아이콘 색이 아니라 **글자 태그**로 (요청) --}}
            <x-filament::badge :color="$server->condition->getColor()" size="sm">
                {{ $server->condition->getLabel() }}
            </x-filament::badge>
            <h2 class="hl-card-title">
                {{ $server->name }}
                <span class="hl-card-uptime">
                    {{ $server->formatResource(\App\Enums\ServerResourceType::Uptime) }}
                </span>
            </h2>
            @if ($actiongroup->isVisible())
                <div class="end-0">
                    <div class="flex-1 dark:bg-gray-800 dark:text-white rounded-b-lg overflow-hidden p-1"
                         x-on:click.stop>
                        {{ $actiongroup }}
                    </div>
                </div>
            @endif
        </div>

        @if ($server->description)
            <div class="text-left hl-card-desc">
                <p class="text-base dark:text-gray-400">{{ Str::limit($server->description, 40, preserveWords: true) }}</p>
            </div>
        @endif

        <div class="flex justify-between text-center items-center hl-card-metrics">
            <div class="w-full hl-metric">
                @php
                    $cpuCurrent = \App\Enums\ServerResourceType::CPU->getResourceAmount($server);
                    $cpuMax = \App\Enums\ServerResourceType::CPULimit->getResourceAmount($server) === 0 ? (($serverNodeSystemInfo['cpu_count'] ?? 0) * 100) : \App\Enums\ServerResourceType::CPULimit->getResourceAmount($server);
                    $getState = fn() => $cpuCurrent;
                    $getMaxValue = fn() => $cpuMax;
                    $getProgressPercentage = fn() => $cpuMax > 0 ? ($cpuCurrent / $cpuMax) * 100 : 0;
                    $getProgressLabel = fn () => $server->formatResource(\App\Enums\ServerResourceType::CPU, 0) . ' / ' . $server->formatResource(\App\Enums\ServerResourceType::CPULimit, 0);
                    $getProgressStatus = fn() => ($cpuMax > 0 && ($cpuCurrent / $cpuMax) * 100 >= ($dangerPercent * 100)) ? 'danger' : (( $cpuMax > 0 && ($cpuCurrent / $cpuMax) * 100 >= ($warningPercent * 100)) ? 'warning' : 'success');
                    $getProgressColor = fn() => $serverEntryColumn->getProgressColorForStatus($getProgressStatus());
                @endphp

                @include('livewire.columns.progress-bar-column', [
                    'getState' => $getState,
                    'getMaxValue' => $getMaxValue,
                    'getProgressPercentage' => $getProgressPercentage,
                    'getProgressLabel' => $getProgressLabel,
                    'getProgressStatus' => $getProgressStatus,
                    'getProgressColor' => $getProgressColor,
                ])
            </div>

            <div class="w-full hl-metric">
                @php
                    $memCurrent = \App\Enums\ServerResourceType::Memory->getResourceAmount($server);
                    $memMax = \App\Enums\ServerResourceType::MemoryLimit->getResourceAmount($server) === 0 ? $serverNodeStatistics['memory_total'] : \App\Enums\ServerResourceType::MemoryLimit->getResourceAmount($server);
                    $getState = fn() => $memCurrent;
                    $getMaxValue = fn() => $memMax > 0 ? $memMax : null;
                    $getProgressPercentage = fn() => ($memMax > 0) ? ($memCurrent / $memMax) * 100 : 0;
                    $getProgressLabel = fn() => $server->formatResource(\App\Enums\ServerResourceType::Memory) . ' / ' . $server->formatResource(\App\Enums\ServerResourceType::MemoryLimit);
                    $getProgressStatus = fn() => ($memMax > 0 && ($memCurrent / $memMax) * 100 >= ($dangerPercent * 100)) ? 'danger' : (( $memMax > 0 && ($memCurrent / $memMax) * 100 >= ($warningPercent * 100)) ? 'warning' : 'success');
                    $getProgressColor = fn() => $serverEntryColumn->getProgressColorForStatus($getProgressStatus());
                @endphp

                @include('livewire.columns.progress-bar-column', [
                    'getState' => $getState,
                    'getMaxValue' => $getMaxValue,
                    'getProgressPercentage' => $getProgressPercentage,
                    'getProgressLabel' => $getProgressLabel,
                    'getProgressStatus' => $getProgressStatus,
                    'getProgressColor' => $getProgressColor,
                ])
            </div>

            <div class="w-full hl-metric">
                @php
                    $diskCurrent = \App\Enums\ServerResourceType::Disk->getResourceAmount($server);
                    $diskMax = \App\Enums\ServerResourceType::DiskLimit->getResourceAmount($server) === 0 ? $serverNodeStatistics['disk_total'] : \App\Enums\ServerResourceType::DiskLimit->getResourceAmount($server);
                    $getState = fn() => $diskCurrent;
                    $getMaxValue = fn() => $diskMax > 0 ? $diskMax : null;
                    $getProgressPercentage = fn() => ($diskMax > 0) ? ($diskCurrent / $diskMax) * 100 : 0;
                    $getProgressLabel = fn() => $server->formatResource(\App\Enums\ServerResourceType::Disk) . ' / ' . $server->formatResource(\App\Enums\ServerResourceType::DiskLimit);
                    $getProgressStatus = fn() => ($diskMax > 0 && ($diskCurrent / $diskMax) * 100 >= ($dangerPercent * 100)) ? 'danger' : (( $diskMax > 0 && ($diskCurrent / $diskMax) * 100 >= ($warningPercent * 100)) ? 'warning' : 'success');
                    $getProgressColor = fn() => $serverEntryColumn->getProgressColorForStatus($getProgressStatus());
                @endphp

                @include('livewire.columns.progress-bar-column', [
                    'getState' => $getState,
                    'getMaxValue' => $getMaxValue,
                    'getProgressPercentage' => $getProgressPercentage,
                    'getProgressLabel' => $getProgressLabel,
                    'getProgressStatus' => $getProgressStatus,
                    'getProgressColor' => $getProgressColor,
                ])
            </div>

            <div class="hidden sm:block">
                <p class="text-sm dark:text-gray-400">{{ trans('server/dashboard.network') }}</p>
                <p class="text-md font-semibold">{{ $server->allocation?->address ?? trans('server/dashboard.none') }}</p>
            </div>
        </div>
    </div>
</div>
