{{-- Hairline 테마의 서버 카드(로딩 중) 오버라이드 (#61).
     ⚠ 실물 카드(server-entry)와 **레이아웃이 같아야** 로딩→완료 전환이 튀지 않는다. --}}
@php
    $backgroundImage = $server->icon ?? $server->egg->icon;
    $serverEntryColumn = $column ?? \App\Filament\Components\Tables\Columns\ServerEntryColumn::make('server_entry');
@endphp

<div class="relative cursor-pointer"
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

            <x-filament::badge color="gray" size="sm">
                {{ trans('server/dashboard.loading') }}
            </x-filament::badge>
            <h2 class="hl-card-title">
                {{ $server->name }}
            </h2>
        </div>

        @if ($server->description)
            <div class="text-left hl-card-desc">
                <p class="text-base dark:text-gray-400">{{ Str::limit($server->description, 40, preserveWords: true) }}</p>
            </div>
        @endif


        <div class="flex justify-between text-center items-center hl-card-metrics">
            <div>
                @php
                    $cpuCurrent = 0;
                    $cpuMax = \App\Enums\ServerResourceType::CPULimit->getResourceAmount($server) ?: 100;
                    $getState = fn() => $cpuCurrent;
                    $getMaxValue = fn() => $cpuMax;
                    $getProgressLabel = fn () => $server->formatResource(App\Enums\ServerResourceType::CPU, 0) . ' / ' . $server->formatResource(App\Enums\ServerResourceType::CPULimit, 0);
                @endphp

                @include('livewire.columns.progress-bar-column', [
                    'getState' => $getState,
                    'getMaxValue' => $getMaxValue,
                    'getProgressLabel' => $getProgressLabel,
                    'getProgressStatus' => fn() => 'success',
                    'getProgressPercentage' => fn () => 0,
                    'getProgressColor' => fn () => $serverEntryColumn->getProgressColorForStatus('success'),
                ])
            </div>

            <div>
                @php
                    $memCurrent = 0;
                    $memMax = \App\Enums\ServerResourceType::MemoryLimit->getResourceAmount($server);
                    $getState = fn() => $memCurrent;
                    $getMaxValue = fn() => $memMax > 0 ? $memMax : null;
                    $getProgressLabel = fn() => convert_bytes_to_readable($memCurrent) . ' / ' . ($memMax > 0 ? convert_bytes_to_readable($memMax) : "\u{221E}");
                @endphp

                @include('livewire.columns.progress-bar-column', [
                    'getState' => $getState,
                    'getMaxValue' => $getMaxValue,
                    'getProgressLabel' => $getProgressLabel,
                    'getProgressStatus' => fn() => 'success',
                    'getProgressPercentage' => fn () => 0,
                    'getProgressColor' => fn () => $serverEntryColumn->getProgressColorForStatus('success'),
                ])
            </div>

            <div>
                @php
                    $diskCurrent = 0;
                    $diskMax = \App\Enums\ServerResourceType::DiskLimit->getResourceAmount($server);
                    $getState = fn() => $diskCurrent;
                    $getMaxValue = fn() => $diskMax > 0 ? $diskMax : null;
                    $getProgressLabel = fn() => convert_bytes_to_readable($diskCurrent) . ' / ' . ($diskMax > 0 ? convert_bytes_to_readable($diskMax) : "\u{221E}");
                @endphp

                @include('livewire.columns.progress-bar-column', [
                    'getState' => $getState,
                    'getMaxValue' => $getMaxValue,
                    'getProgressLabel' => $getProgressLabel,
                    'getProgressStatus' => fn() => 'success',
                    'getProgressPercentage' => fn () => 0,
                    'getProgressColor' => fn () => $serverEntryColumn->getProgressColorForStatus('success'),
                ])
            </div>

            <div class="hidden sm:block">
                <p class="text-sm dark:text-gray-400">{{ trans('server/dashboard.network') }}</p>
                <p class="text-md font-semibold">{{ $server->allocation?->address ?? trans('server/dashboard.none') }}</p>
            </div>
        </div>
    </div>
</div>

