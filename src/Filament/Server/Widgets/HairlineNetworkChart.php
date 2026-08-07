<?php

namespace WisdomIT\Hairline\Filament\Server\Widgets;

use App\Filament\Server\Widgets\ServerNetworkChart;

/** 코어 Network 차트에 테마 스타일만 덮는다 (#61). 데이터·폴링은 부모 그대로. */
class HairlineNetworkChart extends ServerNetworkChart
{
    use HairlineChart;

    protected function chartColor(): string
    {
        return 'rgb(245, 165, 36)';
    }
}
