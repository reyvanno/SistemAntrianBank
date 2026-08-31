<?php

namespace App\Services;

use App\Models\Counter;
use App\Models\Queue;
use App\Models\User;
use Carbon\Carbon;

class DashboardService
{
    private const ACTIVE_QUEUE_STATUSES = [
        'WAITING',
        'CALLED',
        'SERVING',
    ];

    private const COUNTER_QUEUE_STATUSES = [
        'CALLED',
        'SERVING',
    ];

    public function getDashboardData(?User $user = null): array
    {
        $user ??= auth()->user();

        return [
            'statistics' => $this->statistics($user),
            'activeQueues' => $this->activeQueues($user),
            'counterStatus' => $this->counterStatus($user),
            'myCounter' => $this->myCounter($user),
            'today' => now()->translatedFormat('l, d F Y'),
        ];
    }

    private function statistics(User $user): array
    {
        $query = Queue::query()
            ->whereDate('created_at', Carbon::today());

        $this->applyServiceScope(
            $query,
            $user
        );

        $total = (clone $query)->count();

        $active = (clone $query)
            ->whereIn(
                'status',
                self::ACTIVE_QUEUE_STATUSES
            )
            ->count();

        $finished = (clone $query)
            ->where('status', 'FINISHED')
            ->count();

        $average = (clone $query)
            ->whereNotNull('started_at')
            ->whereNotNull('finished_at')
            ->selectRaw("
                AVG(
                    EXTRACT(
                        EPOCH FROM (
                            finished_at - started_at
                        )
                    ) / 60
                ) AS avg_time
            ")
            ->value('avg_time');

        return [
            'total' => $total,
            'active' => $active,
            'finished' => $finished,
            'average' => round($average ?? 0),
        ];
    }

    private function activeQueues(User $user)
    {
        $query = Queue::query()
            ->with([
                'service:id,code,name',
                'counter:id,service_id,code,name',
            ])
            ->whereDate('created_at', Carbon::today())
            ->whereIn(
                'status',
                self::ACTIVE_QUEUE_STATUSES
            )
            ->oldest('id');

        $this->applyServiceScope(
            $query,
            $user
        );

        return $query
            ->take(10)
            ->get();
    }

    private function counterStatus(User $user)
    {
        $query = Counter::query()
            ->with([
                'service:id,code,name',

                'queues' => function ($query) {
                    $query
                        ->whereIn(
                            'status',
                            self::COUNTER_QUEUE_STATUSES
                        )
                        ->latest('updated_at');
                },
            ])
            ->orderBy('code');

        $this->applyServiceScope(
            $query,
            $user
        );

        return $query
            ->get()
            ->map(function (Counter $counter) {

                $activeQueue = $counter->queues->first();

                if (!$counter->is_active) {
                    $status = 'INACTIVE';
                } elseif (!$activeQueue) {
                    $status = 'AVAILABLE';
                } elseif ($activeQueue->status === 'SERVING') {
                    $status = 'SERVING';
                } else {
                    $status = 'CALLED';
                }

                return [
                    'id' => $counter->id,
                    'code' => $counter->code,
                    'name' => $counter->name,
                    'is_active' => $counter->is_active,

                    'service' => [
                        'id' => $counter->service?->id,
                        'code' => $counter->service?->code,
                        'name' => $counter->service?->name,
                    ],

                    'status' => $status,

                    'queue' => $activeQueue
                        ? [
                            'id' => $activeQueue->id,
                            'queue_number' => $activeQueue->queue_number,
                            'status' => $activeQueue->status,
                            'call_count' => $activeQueue->call_count,
                        ]
                        : null,
                ];
            })
            ->values();
    }

    private function myCounter(User $user): ?array
    {
        if ($user->hasRole('admin')) {
            return null;
        }

        $counter = $user->counter()
            ->with('service')
            ->first();

        if (!$counter) {
            return null;
        }

        /*
         * Cari queue aktif yang sedang berada
         * pada loket milik user.
         */
        $queue = Queue::query()
            ->with([
                'service:id,code,name',
                'counter:id,service_id,code,name',
            ])
            ->where('counter_id', $counter->id)
            ->whereIn(
                'status',
                self::COUNTER_QUEUE_STATUSES
            )
            ->latest('updated_at')
            ->first();

        if (!$counter->is_active) {
            $status = 'INACTIVE';
        } elseif (!$queue) {
            $status = 'AVAILABLE';
        } elseif ($queue->status === 'SERVING') {
            $status = 'SERVING';
        } else {
            $status = 'CALLED';
        }

        return [
            'id' => $counter->id,
            'code' => $counter->code,
            'name' => $counter->name,
            'is_active' => $counter->is_active,

            'status' => $status,

            'service' => [
                'id' => $counter->service?->id,
                'code' => $counter->service?->code,
                'name' => $counter->service?->name,
            ],

            'queue' => $queue
                ? [
                    'id' => $queue->id,
                    'queue_number' => $queue->queue_number,
                    'status' => $queue->status,
                    'call_count' => $queue->call_count,
                ]
                : null,
        ];
    }

    /**
     * Admin melihat seluruh service.
     *
     * Teller / CS hanya melihat service
     * yang terhubung dengan loketnya.
     */
    private function applyServiceScope(
        $query,
        User $user
    ): void {
        if ($user->hasRole('admin')) {
            return;
        }

        $serviceId = $user->counter?->service_id;

        if ($serviceId) {
            $query->where(
                'service_id',
                $serviceId
            );
        } else {
            $query->whereRaw('1 = 0');
        }
    }
}