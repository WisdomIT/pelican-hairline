/* 콘솔 터미널 글꼴을 CSS 의 --font-mono 로 맞춘다 (#61).
 *
 * 왜 CSS 로 못 하나 (실측):
 *  - 터미널은 xterm.js 이고 코어가 **WebglAddon** 을 싣는다. 글리프를 텍스처 아틀라스에
 *    구워서 그리므로 #terminal 에 font-family 를 줘도 캔버스 안 글자에는 닿지 않는다.
 *  - 글꼴은 blade 에서 JS 옵션으로 들어간다:
 *      resources/views/filament/components/server-console.blade.php
 *        fontFamily: '{{ $userFont }}, monospace'
 *    이 문자열은 canvas ctx.font 로 넘어가므로 var(--font-mono) 를 써도 해석되지 않는다.
 *    $userFont 는 사용자 프로필 설정(CustomizationKey::ConsoleFont)이고 선택지는
 *    'monospace' + storage/app/public/fonts/*.ttf 뿐이다.
 *
 * 그래서 값을 **런타임에 풀어서** 옵션으로 넣는다. 코어 뷰(150줄)를 통째로 베끼는 대신
 * window.Xterm 이 대입되는 순간을 잡아 Terminal 생성자만 감싼다 — 패널 업그레이드 때
 * 대조할 사본이 늘지 않는다.
 *
 * ⚠ blade 의 @script 안에서 `const { Terminal } = window.Xterm` 로 꺼내 쓰기 때문에
 *   프로퍼티를 바꿔치기하는 것만으로 충분하다. 우리 스크립트는 일반(classic) 인라인이라
 *   defer 되는 console.js 모듈보다 **먼저** 실행되므로 setter 를 놓치지 않는다.
 */
(function () {
    if (window.__hairlineConsoleFont) {
        return;
    }
    window.__hairlineConsoleFont = true;

    function resolveFont() {
        try {
            var value = getComputedStyle(document.documentElement)
                .getPropertyValue('--font-mono')
                .trim();

            return value || null;
        } catch (e) {
            return null;
        }
    }

    function patch(bundle) {
        if (!bundle || typeof bundle.Terminal !== 'function' || bundle.Terminal.__hairlineFont) {
            return bundle;
        }

        var Original = bundle.Terminal;

        // 값은 생성 시점에 읽는다 — 이 스크립트가 실행될 때는 아직 스타일시트가
        // 적용되기 전일 수 있다.
        function Patched(options) {
            var font = resolveFont();

            return new Original(font ? Object.assign({}, options, { fontFamily: font }) : options);
        }

        Patched.prototype = Original.prototype;
        Patched.__hairlineFont = true;
        bundle.Terminal = Patched;

        return bundle;
    }

    var store = window.Xterm;

    try {
        Object.defineProperty(window, 'Xterm', {
            configurable: true,
            get: function () {
                return store;
            },
            set: function (value) {
                store = patch(value);
            },
        });
    } catch (e) {
        return;
    }

    // 이미 실려 있었다면(SPA 재진입) 지금 감싼다.
    if (store) {
        patch(store);
    }
})();
