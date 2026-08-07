<?php

namespace WisdomIT\Hairline\Filament\Server\Widgets;

use App\Filament\Server\Widgets\ServerCpuChart;

/** 코어 Cpu 차트에 테마 스타일만 덮는다 (#61). 데이터·폴링은 부모 그대로. */
class HairlineCpuChart extends ServerCpuChart
{
    use HairlineChart;

    protected function chartColor(): string
    {
        return 'rgb(0, 111, 255)';
    }
}
