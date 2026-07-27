<?php

namespace App\Services;

use App\Models\Counter;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getDashboardData(): array
    {
        return [

            'statistics' => $this->statistics(),

            'activeQueues' => $this->activeQueues(),

            'counterStatus' => $this->counterStatus(),

            'today' => now()->translatedFormat('l, d F Y'),

        ];
    }

    private function statistics(): array
    {
        $today = Carbon::today();

        $totalQueue = Queue::whereDate('created_at', $today)
            ->count();

        $activeQueue = Queue::whereDate('created_at', $today)
            ->whereIn('status', [
                'WAITING',
                'CALLED',
                'SERVING',
            ])
            ->count();

        $finishedQueue = Queue::whereDate('created_at', $today)
            ->where('status', 'FINISHED')
            ->count();

        $averageTime = Queue::query()

            ->whereDate('created_at', $today)

            ->whereNotNull('started_at')

            ->whereNotNull('finished_at')

            ->selectRaw("
                AVG(
                    EXTRACT(
                        EPOCH FROM (
                            finished_at - started_at
                        )
                    ) / 60
                ) as avg_time
            ")

            ->value('avg_time');

        return [

            'total' => $totalQueue,

            'active' => $activeQueue,

            'finished' => $finishedQueue,

            'average' => round($averageTime ?? 0),

        ];
    }

    private function activeQueues()
    {
        return Queue::query()

            ->with([
                'service',
                'counter',
            ])

            ->whereIn('status', [
                'WAITING',
                'CALLED',
                'SERVING',
            ])

            ->oldest()

            ->take(10)

            ->get();
    }

    private function counterStatus()
    {
        return Counter::query()

            ->with([
                'service',
                'queues' => function ($query) {

                    $query

                        ->whereIn(
                            'status',
                        [
                            'CALLED',
                            'SERVING',
                        ])

                        ->latest();

                },
            ])

            ->orderBy('code')

            ->get();
    }
}