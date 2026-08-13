{{-- 속성 표 (#61). 카드 6개 대신 표 하나 — 코어 ServerOverview 는 CSS 로 숨긴다.

     🔴 반드시 <x-filament-widgets::widget> 로 감싼다. 이 컴포넌트가 getColumnSpan() 을
        grid-column 으로 옮긴다(widget.blade: $attributes->gridColumn(...)). 안 감싸면
        columnSpan='full' 이 **적용될 자리가 없어** 그리드에서 1열만 차지한다 — CSS 로
        때우려 했으나 wire:key 는 하이드레이션 후 사라져 폭이 되돌아갔다(실측). --}}
<x-filament-widgets::widget>
<div class="hl-overview" wire:poll.1s>
    <table class="hl-table">
        <tbody>
            <tr>
                <th>{{ trans('server/console.labels.name') }}</th>
                <td>{{ $name }}</td>
                <th>{{ trans('server/console.labels.cpu') }}</th>
                <td>{{ $cpu }}</td>
            </tr>
            <tr>
                <th>{{ trans('server/console.labels.status') }}</th>
                <td><span class="hl-dot hl-{{ $statusColor }}"></span>{{ $status }}</td>
                <th>{{ trans('server/console.labels.memory') }}</th>
                <td>{{ $memory }}</td>
            </tr>
            <tr>
                <th>{{ trans('server/console.labels.address') }}</th>
                <td><code class="hl-mono">{{ $address }}</code></td>
                <th>{{ trans('server/console.labels.disk') }}</th>
                <td>{{ $disk }}</td>
            </tr>
        </tbody>
    </table>
</div>
</x-filament-widgets::widget>
