<?php

namespace WisdomIT\Hairline\Filament\Server\Widgets;

use App\Enums\ContainerStatus;
use App\Models\Server;
use Carbon\CarbonInterface;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

/**
 * 콘솔 상단 오버뷰를 **표 형태**로 (#61).
 *
 * 코어 ServerOverview 는 스탯 카드 6개를 나열한다 — 속성 표로 바꾼다. 코어 위젯은
 * CSS 로 숨기고 이 위젯이 같은 자리(AboveConsole)에 선다. 데이터 접근은 코어와 동일
 * (웹소켓 모니터가 채우는 cache 키) — 숫자가 콘솔 차트와 어긋나면 안 된다.
 */
class HairlineServerOverview extends Widget
{
    protected string $view = 'hairline::server-overview';

    protected static bool $isLazy = false;

    /** 코어가 서 있던 자리를 그대로 차지한다 — 3열 그리드에서 1열만 먹으면 오른쪽이 빈다(실측 피드백). */
    protected int | string | array $columnSpan = 'full';

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        /** @var Server $server */
        $server = Filament::getTenant();

        return [
            'name' => $server->name,
            'status' => $this->status($server),
            'statusColor' => $this->statusColor($server),
            'address' => $server->allocation?->address ?? '-',
            'cpu' => $this->cpuUsage($server),
            'memory' => $this->memoryUsage($server),
            'disk' => $this->diskUsage($server),
        ];
    }

    private function status(Server $server): string
    {
        $status = $server->condition->getLabel();
        $uptime = collect(cache()->get("servers.{$server->id}.uptime"))->last() ?? 0;

        if ($uptime === 0) {
            return $status;
        }

        return $status . ' · ' . now()->subMillis($uptime)
            ->diffForHumans(syntax: CarbonInterface::DIFF_ABSOLUTE, short: true, parts: 2);
    }

    private function statusColor(Server $server): string
    {
        return match (true) {
            $server->retrieveStatus() === ContainerStatus::Running => 'ok',
            $server->retrieveStatus()->isOffline() => 'off',
            default => 'busy',
        };
    }

    private function cpuUsage(Server $server): string
    {
        if ($server->retrieveStatus()->isOffline()) {
            return '-';
        }

        $data = collect(cache()->get("servers.{$server->id}.cpu_absolute"))->last(default: 0);

        return format_number($data, maxPrecision: 2) . ' %'
            . ($server->cpu > 0 ? ' / ' . format_number($server->cpu) . ' %' : '');
    }

    private function memoryUsage(Server $server): string
    {
        if ($server->retrieveStatus()->isOffline()) {
            return '-';
        }

        $used = collect(cache()->get("servers.{$server->id}.memory_bytes"))->last(default: 0);
        $total = $server->memory * (config('panel.use_binary_prefix') ? 1024 * 1024 : 1000 * 1000);

        return convert_bytes_to_readable($used)
            . ($server->memory > 0 ? ' / ' . convert_bytes_to_readable($total) : '');
    }

    private function diskUsage(Server $server): string
    {
        $disk = collect(cache()->get("servers.{$server->id}.disk_bytes"))->last(default: 0);

        if ($disk === 0) {
            return '-';
        }

        $total = $server->disk * (config('panel.use_binary_prefix') ? 1024 * 1024 : 1000 * 1000);

        return convert_bytes_to_readable($disk)
            . ($server->disk > 0 ? ' / ' . convert_bytes_to_readable($total) : '');
    }
}
