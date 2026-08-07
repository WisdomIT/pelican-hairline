<?php

namespace WisdomIT\Hairline\Filament\Server\Widgets;

use App\Filament\Server\Widgets\ServerMemoryChart;

/** 코어 Memory 차트에 테마 스타일만 덮는다 (#61). 데이터·폴링은 부모 그대로. */
class HairlineMemoryChart extends ServerMemoryChart
{
    use HairlineChart;

    protected function chartColor(): string
    {
        return 'rgb(16, 185, 129)';
    }
}
