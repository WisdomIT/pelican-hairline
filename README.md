# Hairline (#61)

그림자를 전부 걷어내고 **1px 헤어라인 보더**로만 면을 나누는, 조밀한 Pelican 테마.
네트워크 장비 콘솔의 정보 밀도를 참고했다. **본체·다른 플러그인 무수정** — 빠지면
원래 모습으로 돌아온다.

## 구조 — 세 레이어

| 레이어 | 방법 | 다루는 것 |
|---|---|---|
| 팔레트 | `FilamentColor::register` 재등록 | primary(#006fff)·gray(그래파이트)·상태색 |
| CSS | 렌더 훅(`STYLES_AFTER`)으로 `<style>` 주입 | 라운드·보더·사이드바·타이포·밀도 |
| JS | 렌더 훅(`SCRIPTS_BEFORE`)으로 `<script>` 주입 | 콘솔 터미널 글꼴(xterm 옵션) |

CSS 클래스 접두사는 `hl-`, 뷰 네임스페이스는 `hairline::`, 주입한 태그에는
`data-hairline` 이 붙는다.

## 네 번째 레이어 — 코어 뷰 오버라이드

CSS 로 못 고치는 **구조**(좌측 상태 세로바, 중첩된 패딩·마진, 유틸리티로 짠 전용 화면)는
뷰를 통째로 갈아끼운다. `View::prependLocation(resources/views-override)` 로 코어보다
먼저 찾게 한다.

| 덮은 뷰 | 이유 |
|---|---|
| `livewire/server-entry` | 서버 카드 — 세로바 제거, 상태를 글자 배지로, 패딩 단일화 |
| `livewire/server-entry-placeholder` | 로딩 중 카드 — **같이 안 덮으면 로딩→완료에서 레이아웃이 튄다** |
| `filament/pages/health` | 상태 페이지 — `fi-*` 가 아니라 Tailwind 유틸리티로 짠 전용 blade 라 CSS 로는 범위를 가둘 수 없다 |

⚠ 코어 뷰의 사본이다 — 패널 업그레이드 때 원본과 대조할 것.

## 하지 말 것 (실측)

- 🔴 **코어 차트 위젯을 상속해 같은 페이지에 함께 세우지 말 것.** 코어를 CSS 로 숨기고
  상속 위젯(`HairlineCpuChart` 등)을 Bottom 에 등록했더니 **콘솔 페이지 전체가 뜨지 않았다**
  (서버 렌더는 정상 — 클라이언트에서 깨진다). 클래스는 남겨 뒀지만 등록은 주석 처리.
  Chart.js 가 Alpine 컴포넌트에 번들돼 전역이 없어 옵션을 바꿀 다른 통로도 없다 —
  차트 스타일은 **미해결**로 남긴다

## 함정 (실측)

- 🔴 **플러그인 프로바이더는 코어보다 먼저 부트된다.** boot 에서 바로 색을 등록하면
  FilamentServiceProvider 가 도로 덮는다 → `$this->app->booted()` 로 미뤄야 이긴다
- 🔴 plugin.json 에 `class`(Filament Plugin 계약)가 **필수**다 — 없으면
  `Undefined array key "class"` 로 errored. providers 키는 스키마에 없고
  `src/Providers/` 가 자동 발견된다. `id` 는 **루트 폴더명과 같아야** 한다
- `panel_version` 은 캐럿 필수 (정확 일치 함정 — wisdom-agent 에서 실측)
- CSS 는 Filament 마크업(`fi-*`)에 의존한다 — 패널 메이저 업그레이드 때 확인
- 색은 hex 로 등록해도 Filament 이 **oklch 로 변환**해 노출한다 — 검증할 때 참고
- 🔴 **위젯 폭은 CSS 가 아니라 `<x-filament-widgets::widget>` 래퍼로.** 그 컴포넌트가
  `getColumnSpan()` 을 grid-column 으로 옮긴다. 감싸지 않으면 `columnSpan='full'` 이
  적용될 자리가 없다. CSS 우회는 `wire:key` 가 하이드레이션 후 사라져 실패한다
- 🔴 **서버 목록 테이블은 지연 로드**다(`isTableLoaded=false`). 서버 렌더로 마크업을
  확인할 때는 `->set('isTableLoaded', true)` 를 줘야 레코드가 나온다 — 이걸 몰라
  "클래스가 없다"고 오판하고 존재하지 않는 선택자로 CSS 를 썼다
- 🔴 **Filament 은 테두리를 ring(=box-shadow)으로 그리는 곳이 많다.** 그림자를 없애려고
  `box-shadow: none` 을 주면 경계까지 함께 사라진다(`.fi-btn`·`.fi-fo-repeater-item` 실측)
- 🔴 **커스텀 속성이 인라인 style 에 박혀 있으면 스타일시트가 진다.** 격자 열 수는
  `--cols-lg` 를 덮는 게 아니라 `grid-template-columns` 를 직접 덮어야 한다
- 위젯 순서는 `ConsoleWidgetPosition` 으로 정한다. `AboveConsole` 은 다른 플러그인과
  같은 칸이라 부트 순서에 밀린다 — 먼저 세우려면 `Top`

## 배포

```bash
bash optional/pelican/plugins/deploy.sh hairline-theme
```

## 허브 제출 전 체크리스트

- [ ] `plugin.json` 의 `meta` 블록 제거 (배포본에만)
- [ ] 라이선스 파일 추가 (MIT 또는 GPL v3 권장)
- [ ] 스크린샷
- [ ] 폴더 통째로 zip — `id` 와 폴더명이 `hairline-theme` 로 일치하는지 확인
