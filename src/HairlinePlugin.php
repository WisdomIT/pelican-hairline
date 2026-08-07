<?php

namespace WisdomIT\Hairline;

use Filament\Contracts\Plugin;
use Filament\Panel;

/**
 * Filament 플러그인 계약 (#61). 패널별 등록이 필요한 게 없어 비어 있다 —
 * 팔레트·CSS 는 프로바이더(HairlineProvider)가 전역으로 처리한다.
 * (plugin.json 의 `class` 는 필수 스키마다 — 없으면 "Undefined array key" 로 죽는다. 실측)
 */
class HairlinePlugin implements Plugin
{
    public function getId(): string
    {
        return 'hairline';
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}
}
