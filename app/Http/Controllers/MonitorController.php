<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;

class MonitorController extends Controller
{
    /**
     * Masa hidup presence petugas.
     *
     * Harus sama dengan CounterPresenceController.
     */
    private const PRESENCE_TTL = 90;

    /**
     * Menampilkan halaman monitor.
     */
    public function index()
    {
        return inertia(
            'Monitor',
            $this->getMonitorData()
        );
    }

    /**
     * Mengambil data monitor terbaru.
     *
     * Digunakan oleh Monitor.vue
     * melalui polling setiap 1.5 detik.
     */
    public function data(): JsonResponse
    {
        return response()->json(
            $this->getMonitorData()
        );
    }

    /**
     * Mengambil seluruh data yang dibutuhkan monitor.
     */
    private function getMonitorData(): array
    {
        /*
        |--------------------------------------------------------------------------
        | COUNTERS
        |--------------------------------------------------------------------------
        |
        | Kita TIDAK hanya mengambil counter yang is_active = true.
        |
        | Monitor perlu mengetahui apakah counter:
        |
        | - Tersedia
        | - Dipanggil
        | - Sedang melayani
        | - Tidak aktif
        |
        | Status "Tidak Aktif" sekarang ditentukan dari
        | presence petugas di Redis.
        |
        */

        $counters = Counter::query()
            ->with([
                'service',

                /*
                 * Ambil seluruh user yang terhubung
                 * dengan counter.
                 *
                 * Karena satu counter bisa memiliki
                 * banyak petugas.
                 */
                'users:id,counter_id,name,is_active',

                /*
                 * Ambil antrian aktif pada counter.
                 */
                'queues' => function ($query) {
                    $query
                        ->whereIn('status', [
                            'CALLED',
                            'SERVING',
                        ])
                        ->latest('updated_at')
                        ->limit(1);
                },
            ])
            ->orderBy('code')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | MAP COUNTER STATUS
        |--------------------------------------------------------------------------
        */

        $counters = $counters
            ->map(function (Counter $counter) {

                /*
                 * Ambil antrian aktif terbaru.
                 */
                $activeQueue = $counter->queues->first();

                /*
                 * Hitung petugas yang:
                 *
                 * 1. User-nya aktif di database
                 * 2. Memiliki presence Redis
                 *
                 * Minimal satu petugas online
                 * berarti counter dianggap online.
                 */
                $onlineUsers = $counter->users
                    ->filter(function (User $user) {

                        return $user->is_active
                            && $this->isUserOnline(
                                $user->id
                            );
                    });

                $onlineStaffCount = $onlineUsers->count();

                /*
                 * Jumlah seluruh petugas aktif
                 * yang terhubung ke counter.
                 */
                $staffCount = $counter->users
                    ->where('is_active', true)
                    ->count();

                /*
                |--------------------------------------------------------------------------
                | TENTUKAN STATUS COUNTER
                |--------------------------------------------------------------------------
                |
                | Prioritas:
                |
                | 1. Counter dinonaktifkan admin
                |      -> INACTIVE
                |
                | 2. Tidak ada petugas online
                |      -> INACTIVE
                |
                | 3. Ada petugas online + SERVING
                |      -> SERVING
                |
                | 4. Ada petugas online + CALLED
                |      -> CALLED
                |
                | 5. Ada petugas online tanpa antrian
                |      -> AVAILABLE
                |
                */

                if (!$counter->is_active) {

                    $status = 'INACTIVE';

                } elseif ($onlineStaffCount === 0) {

                    $status = 'INACTIVE';

                } elseif ($activeQueue) {

                    if ($activeQueue->status === 'SERVING') {

                        $status = 'SERVING';

                    } else {

                        $status = 'CALLED';
                    }

                } else {

                    $status = 'AVAILABLE';
                }

                return [
                    'id' => $counter->id,

                    'code' => $counter->code,

                    'name' => $counter->name,

                    'is_active' => $counter->is_active,

                    /*
                     * Informasi petugas.
                     */
                    'staff_count' => $staffCount,

                    'online_staff_count' => $onlineStaffCount,

                    /*
                     * Informasi service.
                     */
                    'service' => [
                        'id' => $counter->service?->id,

                        'code' => $counter->service?->code,

                        'name' => $counter->service?->name,
                    ],

                    /*
                     * Status yang akan digunakan
                     * oleh CounterCard.vue.
                     */
                    'status' => $status,

                    /*
                     * Antrian aktif.
                     */
                    'queue' => $activeQueue
                        ? [
                            'id' => $activeQueue->id,

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

        /*
        |--------------------------------------------------------------------------
        | WAITING QUEUES
        |--------------------------------------------------------------------------
        */

        $waitingQueues = Queue::query()
            ->with('service')
            ->where('status', 'WAITING')
            ->orderBy('id')
            ->get()
            ->groupBy(
                fn($queue) =>
                $queue->service?->name ?? 'Layanan'
            );

        /*
        |--------------------------------------------------------------------------
        | LATEST CALL
        |--------------------------------------------------------------------------
        */

        $latestCall = Queue::query()
            ->with([
                'service',
                'counter',
            ])
            ->where('status', 'CALLED')
            ->whereNotNull('called_at')
            ->latest('called_at')
            ->first();

        return [
            'counters' => $counters,

            'waitingQueues' => $waitingQueues,

            'latestCall' => $latestCall
                ? [
                    'id' =>
                        $latestCall->id,

                    'queue_number' =>
                        $latestCall->queue_number,

                    'service' =>
                        $latestCall->service?->name,

                    'counter' =>
                        $latestCall->counter?->code,

                    'call_count' =>
                        $latestCall->call_count,

                    'called_at' =>
                        $latestCall->called_at
                                ?->toISOString(),
                ]
                : null,
        ];
    }

    /**
     * Mengecek apakah user memiliki
     * presence aktif di Redis.
     *
     * Presence dibuat oleh:
     *
     * admin.counter.heartbeat
     *
     * dan TTL-nya 90 detik.
     */
    private function isUserOnline(int $userId): bool
    {
        return (bool) Redis::exists(
            $this->presenceKey($userId)
        );
    }

    /**
     * Key Redis presence user.
     */
    private function presenceKey(int $userId): string
    {
        return "counter_presence:user:{$userId}";
    }
}