<?php

namespace WisdomIT\Hairline\Providers;

use App\Enums\ConsoleWidgetPosition;
use App\Filament\Server\Pages\Console;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use WisdomIT\Hairline\Filament\Server\Widgets\HairlineCpuChart;
use WisdomIT\Hairline\Filament\Server\Widgets\HairlineMemoryChart;
use WisdomIT\Hairline\Filament\Server\Widgets\HairlineNetworkChart;
use WisdomIT\Hairline\Filament\Server\Widgets\HairlineServerOverview;

/**
 * 네트워크 장비 콘솔의 정보 밀도를 참고한 테마 (#61).
 *
 * 두 레이어로 동작한다:
 *  1. **팔레트 재등록** — 코어(FilamentServiceProvider)가 등록한 색을 boot 시점에 덮어쓴다.
 *     플러그인 프로바이더는 코어 이후에 부트되므로 나중 등록이 이긴다(실측 전제 — #61).
 *  2. **CSS 주입** — 렌더 훅으로 <style> 을 넣는다. 팔레트로 못 바꾸는 것(라운드·보더·
 *     사이드바 구조·타이포)은 여기서 다룬다. wisdom-agent 사이드바와 같은 방식이라
 *     에셋 파이프라인이 필요 없다.
 *
 * ⚠ 본체·다른 플러그인을 일절 수정하지 않는다 — 이 플러그인이 빠지면 원래 모습이다.
 */
class HairlineProvider extends ServiceProvider
{
    public function boot(): void
    {
        // ⚠ 플러그인 프로바이더는 **코어보다 먼저** 부트된다(실측 — 여기서 바로 등록하면
        //   FilamentServiceProvider 가 도로 덮는다). 전체 부트가 끝난 뒤에 등록해야 이긴다.
        $this->app->booted(fn () => $this->registerPalette());

        FilamentView::registerRenderHook(
            PanelsRenderHook::STYLES_AFTER,
            fn (): string => $this->styles(),
        );

        // 콘솔 터미널 글꼴(#61). xterm 은 WebGL 로 글리프를 굽기 때문에 CSS 가 닿지 않는다 —
        // --font-mono 를 런타임에 풀어 Terminal 옵션으로 넣는다. 자세한 이유는 JS 주석 참고.
        //  ⚠ **SCRIPTS_BEFORE** 여야 한다. console.js 는 @vite 모듈이라 defer 되므로,
        //    일반 인라인 스크립트인 우리 쪽이 먼저 실행돼 window.Xterm 대입을 잡을 수 있다.
        FilamentView::registerRenderHook(
            PanelsRenderHook::SCRIPTS_BEFORE,
            fn (): string => $this->consoleFontScript(),
        );

        // 표 형태 오버뷰(#61) — 코어 스탯 카드는 CSS 가 숨기고 이 위젯이 그 자리를 대신한다.
        //  ⚠ **Top 이어야 한다.** AboveConsole 은 접속 인원 위젯(#53)과 같은 칸이라 등록
        //    순서(플러그인 부트 순서)에 밀려 상태가 아래로 갔다. Top 은 코어 오버뷰보다도
        //    앞자리라 순서가 고정된다 — 상태가 먼저, 접속 인원이 그 다음.
        $this->loadViewsFrom(dirname(__DIR__, 2) . '/resources/views', 'hairline');

        // 🔴 코어 뷰 오버라이드: prependLocation 은 **이름 없는 뷰**를 여기서 먼저 찾게 한다.
        //    서버 카드(livewire/server-entry)는 Filament 컴포넌트가 아니라 Pelican 전용
        //    blade 라 CSS 로는 구조(세로바·중첩 패딩)를 못 고친다 — 뷰째로 갈아끼운다.
        //    ⚠ 패널 업그레이드 때 원본과 대조할 것.
        View::prependLocation(dirname(__DIR__, 2) . '/resources/views-override');
        Console::registerCustomWidgets(ConsoleWidgetPosition::Top, [HairlineServerOverview::class]);

        // ⚠ 차트 교체는 **일시 중단**한다(#61). 코어 차트를 CSS 로 숨기고 상속 위젯을 같은
        //   자리에 세웠더니 콘솔 터미널이 뜨지 않는다는 보고를 받았다(서버 렌더는 정상 —
        //   클라이언트 쪽에서 깨진다). 원인을 가른 뒤 다시 켠다. 클래스는 그대로 둔다.
        // Console::registerCustomWidgets(ConsoleWidgetPosition::Bottom, [
        //     HairlineCpuChart::class, HairlineMemoryChart::class, HairlineNetworkChart::class,
        // ]);
    }

    private function registerPalette(): void
    {
        // ── 시그니처 블루. Filament 이 shade 별로 골라 쓰므로 전 단계를 준다.
        FilamentColor::register([
            'primary' => [
                50 => '#e5f0ff',
                100 => '#cce1ff',
                200 => '#99c3ff',
                300 => '#66a5ff',
                400 => '#3387ff',
                500 => '#006fff',   // ← 시그니처 블루
                600 => '#005ce6',
                700 => '#0049b8',
                800 => '#00378a',
                900 => '#00255c',
                950 => '#001737',
            ],
            // ── 그래파이트 스케일. 다크 모드의 배경·카드·보더가 전부 이 스케일에서 나온다.
            //    순수 회색이 아니라 살짝 푸른 그래파이트다.
            'gray' => [
                50 => '#f7f8fa',
                100 => '#eef0f3',
                200 => '#dfe2e7',
                300 => '#c3c8d0',
                400 => '#9ba1aa',
                500 => '#7c828c',
                600 => '#5c626c',
                700 => '#3a3f47',   // 보더
                800 => '#252930',   // 카드
                900 => '#1b1e24',   // 콘텐츠 배경
                950 => '#121418',   // 사이드바(거의 검정)
            ],
            'success' => Color::hex('#38cc65'),
            'warning' => Color::hex('#f5a524'),
            'danger' => Color::hex('#f0383b'),
            'info' => Color::hex('#00b2ff'),
        ]);
    }

    private function styles(): string
    {
        $css = @file_get_contents(dirname(__DIR__, 2) . '/resources/theme.css');

        return $css === false ? '' : '<style data-hairline>' . $css . '</style>';
    }

    private function consoleFontScript(): string
    {
        $js = @file_get_contents(dirname(__DIR__, 2) . '/resources/console-font.js');

        return $js === false ? '' : '<script data-hairline>' . $js . '</script>';
    }
}
