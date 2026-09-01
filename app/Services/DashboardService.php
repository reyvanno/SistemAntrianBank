<?php

namespace App\Services;

use App\Models\Counter;
use App\Models\Queue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;

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

    /**
     * Presence dianggap online selama TTL ini.
     */
    private const PRESENCE_TTL = 90;

    /**
     * Ambil seluruh data dashboard.
     */
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

    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

    private function statistics(User $user): array
    {
        $query = Queue::query()
            ->whereDate(
                'created_at',
                Carbon::today()
            );

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
            ->where(
                'status',
                'FINISHED'
            )
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

    /*
    |--------------------------------------------------------------------------
    | Active Queues
    |--------------------------------------------------------------------------
    */

    private function activeQueues(User $user)
    {
        $query = Queue::query()
            ->with([
                'service:id,code,name',
                'counter:id,service_id,code,name',
            ])
            ->whereDate(
                'created_at',
                Carbon::today()
            )
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

    /*
    |--------------------------------------------------------------------------
    | Counter Status
    |--------------------------------------------------------------------------
    */

    private function counterStatus(User $user)
    {
        $query = Counter::query()
            ->with([
                'service:id,code,name',

                /*
                 * Semua petugas yang terhubung
                 * dengan counter.
                 */
                'users:id,counter_id,name,is_active',

                /*
                 * Queue aktif pada counter.
                 */
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

                /*
                 * Queue aktif terbaru.
                 */
                $activeQueue = $counter->queues->first();

                /*
                 * Semua user aktif secara database.
                 */
                $activeStaff = $counter->users
                    ->filter(
                        fn(User $staff) =>
                        $staff->is_active
                    );

                /*
                 * Dari user aktif tersebut,
                 * cek siapa yang benar-benar mempunyai
                 * presence di Redis.
                 */
                $onlineUsers = $activeStaff
                    ->filter(
                        fn(User $staff) =>
                        $this->isUserOnline(
                            $staff->id
                        )
                    );

                $staffCount = $activeStaff->count();

                $onlineStaffCount = $onlineUsers->count();

                /*
                 * ------------------------------------------------------
                 * Tentukan status counter
                 * ------------------------------------------------------
                 *
                 * 0 online
                 *      = INACTIVE
                 *
                 * >=1 online + tidak ada queue
                 *      = AVAILABLE
                 *
                 * >=1 online + CALLED
                 *      = CALLED
                 *
                 * >=1 online + SERVING
                 *      = SERVING
                 *
                 */

                if (
                    !$counter->is_active ||
                    $onlineStaffCount === 0
                ) {
                    $status = 'INACTIVE';

                } elseif ($activeQueue) {

                    $status =
                        $activeQueue->status === 'SERVING'
                        ? 'SERVING'
                        : 'CALLED';

                } else {

                    $status = 'AVAILABLE';
                }

                return [
                    'id' => $counter->id,

                    'code' => $counter->code,

                    'name' => $counter->name,

                    'is_active' => $counter->is_active,

                    /*
                     * Jumlah petugas aktif di database.
                     */
                    'staff_count' => $staffCount,

                    /*
                     * Jumlah petugas yang benar-benar online.
                     */
                    'online_staff_count' => $onlineStaffCount,

                    'service' => [
                        'id' => $counter->service?->id,

                        'code' => $counter->service?->code,

                        'name' => $counter->service?->name,
                    ],

                    'status' => $status,

                    'queue' => $activeQueue
                        ? [
                            'id' =>
                                $activeQueue->id,

                            'queue_number' =>
                                $activeQueue->queue_number,

                            'status' =>
                                $activeQueue->status,

                            'call_count' =>
                                $activeQueue->call_count,
                        ]
                        : null,
                ];
            })
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | My Counter
    |--------------------------------------------------------------------------
    */

    private function myCounter(User $user): ?array
    {
        /*
         * Admin tidak mempunyai counter sendiri.
         */
        if ($user->hasRole('admin')) {
            return null;
        }

        /*
         * Ambil counter milik user.
         */
        $counter = $user->counter()
            ->with('service')
            ->first();

        if (!$counter) {
            return null;
        }

        /*
         * Queue yang sedang ditangani
         * oleh user ini.
         */
        $queue = Queue::query()
            ->with([
                'service:id,code,name',
                'counter:id,service_id,code,name',
            ])
            ->where(
                'counter_id',
                $counter->id
            )
            ->where(
                'handled_by',
                $user->id
            )
            ->whereIn(
                'status',
                self::COUNTER_QUEUE_STATUSES
            )
            ->latest('updated_at')
            ->first();

        /*
         * User dianggap online jika:
         *
         * 1. user aktif
         * 2. counter aktif
         * 3. Redis presence masih ada
         */
        $isOnline =
            $user->is_active &&
            $counter->is_active &&
            $this->isUserOnline(
                $user->id
            );

        /*
         * Tentukan status My Counter.
         */
        if (!$isOnline) {

            $status = 'INACTIVE';

        } elseif ($queue) {

            $status =
                $queue->status === 'SERVING'
                ? 'SERVING'
                : 'CALLED';

        } else {

            $status = 'AVAILABLE';
        }

        return [
            'id' => $counter->id,

            'code' => $counter->code,

            'name' => $counter->name,

            'is_active' => $counter->is_active,

            'online' => $isOnline,

            'service' => [
                'id' => $counter->service?->id,

                'code' => $counter->service?->code,

                'name' => $counter->service?->name,
            ],

            'status' => $status,

            'queue' => $queue
                ? [
                    'id' =>
                        $queue->id,

                    'queue_number' =>
                        $queue->queue_number,

                    'status' =>
                        $queue->status,

                    'call_count' =>
                        $queue->call_count,
                ]
                : null,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Presence
    |--------------------------------------------------------------------------
    */

    private function isUserOnline(int $userId): bool
    {
        return (bool) Redis::exists(
            $this->presenceKey($userId)
        );
    }

    private function presenceKey(int $userId): string
    {
        return "counter_presence:user:{$userId}";
    }

    /*
    |--------------------------------------------------------------------------
    | Service Scope
    |--------------------------------------------------------------------------
    */

    private function applyServiceScope(
        $query,
        User $user
    ): void {
        /*
         * Admin melihat semua service.
         */
        if ($user->hasRole('admin')) {
            return;
        }

        /*
         * Teller / CS hanya melihat service
         * yang dimiliki counter-nya.
         */
        $serviceId = $user->counter?->service_id;

        if ($serviceId) {

            $query->where(
                'service_id',
                $serviceId
            );

        } else {

            $query->whereRaw(
                '1 = 0'
            );
        }
    }
}