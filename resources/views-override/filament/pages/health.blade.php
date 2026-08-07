{{--
    상태(Health) 페이지 — Hairline 테마 (#61)

    ⚠ 코어 뷰(resources/views/filament/pages/health.blade.php)의 대체본이다.
      패널 업그레이드 때 원본과 대조할 것.

    왜 CSS 가 아니라 뷰인가: 이 페이지는 fi-* 컴포넌트가 아니라 Tailwind 유틸리티로
    직접 짠 전용 blade 다(shadow-lg·rounded-xl·bg-white·ring-1 …). CSS 로 고치려면
    유틸리티 클래스를 덮어야 하는데, 그건 같은 클래스를 쓰는 다른 화면까지 전부
    끌고 간다 — 범위를 가둘 방법이 없다.

    바뀐 것: 카드 격자 → **헤어라인 한 상자 안의 행 목록**(콘솔 오버뷰 .hl-table 과
    같은 결). 상태는 큰 원형 아이콘 대신 색 점으로, 이름·요약에 위계를 준다.
    PHP 쪽 접점($this->icon/iconColor, $checkResults, $lastRanAt)은 그대로 쓴다.
--}}
<x-filament-panels::page>
    @if (count($checkResults?->storedCheckResults ?? []))
        <div class="hl-health">
            @foreach ($checkResults->storedCheckResults as $result)
                <div class="hl-health-row">
                    <x-filament::icon
                        icon="{{ $this->icon($result->status) }}"
                        class="hl-health-icon {{ $this->iconColor($result->status) }}"
                    />

                    <span class="hl-health-name">
                        {{ trans('admin/health.results.' . preg_replace('/\s+/', '', mb_strtolower($result->label)) . '.label') }}
                    </span>

                    <span class="hl-health-summary">
                        {{ filled($result->notificationMessage) ? $result->notificationMessage : $result->shortSummary }}
                    </span>
                </div>
            @endforeach
        </div>
    @endif

    @if ($lastRanAt)
        <p @class(['hl-health-time', 'hl-health-stale' => $lastRanAt->diffInMinutes() > 5])>
            {{ trans('admin/health.checked', ['time' => $lastRanAt->diffForHumans()]) }}
        </p>
    @endif
</x-filament-panels::page>
