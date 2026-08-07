<?php

namespace WisdomIT\Hairline\Filament\Server\Widgets;

use Filament\Support\RawJs;

/**
 * 코어 차트 위젯에 테마 스타일을 입히는 공통 부분 (#61).
 *
 * ⚠ Chart.js 는 Filament 의 Alpine 컴포넌트 안에 번들돼 있어 **전역(window.Chart)이 없다**
 *   — CSS 로도, 전역 defaults 로도 손댈 수 없다(실측). 색과 옵션은 위젯의 PHP
 *   getData()/getOptions() 가 유일한 통로라, 코어 위젯을 상속해 그 둘만 덮는다.
 *   데이터 수집(캐시 키·폴링)은 부모 것을 그대로 쓴다.
 */
trait HairlineChart
{
    /** 라인 색. 자식이 정한다. */
    abstract protected function chartColor(): string;

    /** @return array<string, mixed> */
    protected function getData(): array
    {
        $data = parent::getData();
        $color = $this->chartColor();

        foreach ($data['datasets'] ?? [] as $i => $dataset) {
            $data['datasets'][$i] = array_merge($dataset, [
                'borderColor' => $color,
                // 라인 아래 반투명 채우기 — 가이드의 "Alpha Gradient" 근사.
                'backgroundColor' => str_replace('rgb(', 'rgba(', rtrim($color, ')')) . ', 0.12)',
                'borderWidth' => 1.5,
                'tension' => 0.35,
                'fill' => true,
                'pointRadius' => 0,
                'pointHoverRadius' => 3,
            ]);
        }

        return $data;
    }

    protected function getOptions(): RawJs
    {
        // 가이드: 최소한의 연한 점선 그리드, X축은 숨김, 범례 없음.
        return RawJs::make(<<<'JS'
        {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 250 },
            interaction: { intersect: false, mode: 'index' },
            scales: {
                y: {
                    min: 0,
                    border: { display: false },
                    grid: { color: 'rgba(124,130,140,0.16)', drawTicks: false, borderDash: [3, 3] },
                    ticks: { font: { size: 10 }, color: '#9ba1aa', maxTicksLimit: 4, padding: 6 },
                },
                x: { display: false },
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    displayColors: false,
                    backgroundColor: 'rgba(20,22,26,0.92)',
                    padding: 8,
                    titleFont: { size: 11 },
                    bodyFont: { size: 11 },
                },
            },
        }
        JS);
    }
}
