<?php

namespace App\Services;

use App\Models\Counter;
use App\Models\Queue;
use App\Models\Service;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class QueueService
{
    /**
     * Maksimal pemanggilan satu nomor.
     */
    private const MAX_CALL_COUNT = 3;

    /**
     * Status queue yang dianggap masih aktif.
     */
    private const ACTIVE_STATUSES = [
        'WAITING',
        'CALLED',
        'SERVING',
    ];

    /**
     * Status queue yang sedang menggunakan loket.
     */
    private const COUNTER_ACTIVE_STATUSES = [
        'CALLED',
        'SERVING',
    ];

    /**
     * Status yang ditampilkan pada halaman
     * antrian Teller / Customer Service.
     */
    private const STAFF_VISIBLE_STATUSES = [
        'WAITING',
        'CALLED',
        'SERVING',
        'FINISHED',
        'CANCELLED',
        'SKIPPED',
    ];

    /*
    |--------------------------------------------------------------------------
    | INDEX / LIST
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan daftar antrian untuk admin/staff.
     *
     * ADMIN:
     *     - semua service
     *     - semua status
     *
     * TELLER / CS:
     *     - hanya service counter sendiri
     *     - hanya WAITING, FINISHED, CANCELLED
     */
    public function paginate(
        User $user,
        ?string $search = null
    ): LengthAwarePaginator {
        $query = Queue::query()
            ->with([
                'service:id,code,name',
                'counter:id,service_id,code,name',
                'handledBy:id,name,username',
            ])
            ->when(
                filled($search),
                function ($query) use ($search) {
                    $query->where(
                        'queue_number',
                        'ILIKE',
                        '%' . trim($search) . '%'
                    );
                }
            )
            ->latest('id');

        /*
         * ADMIN
         *
         * Admin dapat melihat seluruh
         * service dan seluruh status.
         */
        if ($user->hasRole('admin')) {
            return $query
                ->paginate(10)
                ->withQueryString();
        }

        /*
         * Ambil service dari counter user.
         */
        $serviceId = $user->counter?->service_id;

        /*
         * Kalau user tidak mempunyai counter,
         * jangan tampilkan data apa pun.
         */
        if (!$serviceId) {
            $query->whereRaw('1 = 0');

            return $query
                ->paginate(10)
                ->withQueryString();
        }

        /*
         * Batasi berdasarkan service.
         */
        $query->where(
            'service_id',
            $serviceId
        );

        /*
         * Teller / CS hanya melihat:
         *
         * WAITING
         * FINISHED
         * CANCELLED
         */
        $query->whereIn(
            'status',
            self::STAFF_VISIBLE_STATUSES
        );

        return $query
            ->paginate(10)
            ->withQueryString();
    }

    /*
    |--------------------------------------------------------------------------
    | AVAILABLE SERVICES
    |--------------------------------------------------------------------------
    */

    /**
     * Service yang boleh digunakan user
     * untuk membuat nomor antrian.
     *
     * ADMIN:
     *     semua service.
     *
     * TELLER / CS:
     *     hanya service counter sendiri.
     */
    public function availableServices(
        User $user
    ) {
        $query = Service::query()
            ->orderBy('name');

        /*
         * Admin boleh melihat semua service.
         */
        if ($user->hasRole('admin')) {
            return $query->get([
                'id',
                'code',
                'name',
            ]);
        }

        /*
         * User biasa hanya boleh
         * menggunakan service counter-nya.
         */
        $serviceId = $user->counter?->service_id;

        if (!$serviceId) {
            return collect();
        }

        return $query
            ->whereKey($serviceId)
            ->get([
                'id',
                'code',
                'name',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    /**
     * Membuat queue dengan validasi
     * berdasarkan user.
     */
    public function createForUser(
        User $user,
        array $data
    ): Queue {
        /*
         * Admin bebas membuat queue
         * untuk service mana pun.
         */
        if ($user->hasRole('admin')) {
            return $this->create($data);
        }

        /*
         * User non-admin harus mempunyai counter.
         */
        $serviceId = $user->counter?->service_id;

        if (!$serviceId) {
            throw new \RuntimeException(
                'Anda belum memiliki loket.'
            );
        }

        /*
         * Pastikan service yang dikirim
         * sama dengan service counter user.
         */
        if ((int) $data['service_id'] !== (int) $serviceId) {
            throw new \RuntimeException(
                'Anda tidak dapat membuat antrian untuk layanan lain.'
            );
        }

        return $this->create($data);
    }

    /**
     * Membuat nomor antrian baru.
     *
     * WAITING
     */
    public function create(array $data): Queue
    {
        return DB::transaction(function () use ($data) {

            /*
             * Service dikunci agar proses pembuatan nomor
             * untuk service yang sama berjalan serial.
             */
            $service = Service::query()
                ->whereKey($data['service_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $today = Carbon::today();

            $lastQueue = Queue::query()
                ->where(
                    'service_id',
                    $service->id
                )
                ->whereDate(
                    'created_at',
                    $today
                )
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $number = 1;

            if ($lastQueue) {
                $lastNumber = (int) preg_replace(
                    '/\D/',
                    '',
                    $lastQueue->queue_number
                );

                $number = $lastNumber + 1;
            }

            $queueNumber =
                $service->code .
                str_pad(
                    $number,
                    3,
                    '0',
                    STR_PAD_LEFT
                );

            $queue = Queue::create([
                'queue_number' => $queueNumber,
                'service_id' => $service->id,
                'status' => 'WAITING',
                'call_count' => 0,
            ]);

            $this->log(
                $queue,
                'CREATED',
                null,
                "Nomor {$queue->queue_number} dibuat untuk layanan {$service->name}."
            );

            return $queue->fresh([
                'service',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | CALL NEXT
    |--------------------------------------------------------------------------
    */

    /**
     * Memanggil nomor berikutnya.
     *
     * WAITING -> CALLED
     */
    public function callNext(User $user): Queue
    {
        return DB::transaction(function () use ($user) {

            $counter = $this->getActiveCounter($user);

            /*
             * Satu loket hanya boleh mempunyai
             * satu queue aktif.
             */
            $activeQueue = Queue::query()
                ->where(
                    'counter_id',
                    $counter->id
                )
                ->whereIn(
                    'status',
                    self::COUNTER_ACTIVE_STATUSES
                )
                ->lockForUpdate()
                ->first();

            if ($activeQueue) {
                throw new \RuntimeException(
                    "Loket {$counter->code} masih melayani antrian {$activeQueue->queue_number}."
                );
            }

            /*
             * Ambil queue WAITING paling lama
             * dari service loket.
             */
            $queue = Queue::query()
                ->where(
                    'service_id',
                    $counter->service_id
                )
                ->whereDate(
                    'created_at',
                    Carbon::today()
                )
                ->where(
                    'status',
                    'WAITING'
                )
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (!$queue) {
                throw new \RuntimeException(
                    'Tidak ada antrian yang menunggu.'
                );
            }

            $queue->update([
                'counter_id' => $counter->id,
                'handled_by' => $user->id,
                'status' => 'CALLED',
                'call_count' => 1,
                'called_at' => now(),
            ]);

            $this->log(
                $queue,
                'CALLED',
                $user->id,
                "Nomor {$queue->queue_number} dipanggil ke loket {$counter->code}. Panggilan ke-1."
            );

            return $queue->fresh([
                'service',
                'counter',
                'handledBy',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RECALL
    |--------------------------------------------------------------------------
    */

    /**
     * Memanggil ulang queue.
     *
     * CALLED -> CALLED
     */
    public function recall(User $user): Queue
    {
        return DB::transaction(function () use ($user) {

            $queue = $this->getUserActiveQueue(
                $user,
                ['CALLED']
            );

            if (
                $queue->call_count >=
                self::MAX_CALL_COUNT
            ) {
                throw new \RuntimeException(
                    'Antrian sudah mencapai batas maksimal 3 kali pemanggilan.'
                );
            }

            $newCallCount =
                $queue->call_count + 1;

            $queue->update([
                'call_count' => $newCallCount,
                'called_at' => now(),
            ]);

            $this->log(
                $queue,
                'RECALLED',
                $user->id,
                "Nomor {$queue->queue_number} dipanggil ulang ke loket {$queue->counter->code}. Panggilan ke-{$newCallCount}."
            );

            return $queue->fresh([
                'service',
                'counter',
                'handledBy',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | START
    |--------------------------------------------------------------------------
    */

    /**
     * Memulai pelayanan.
     *
     * CALLED -> SERVING
     */
    public function start(User $user): Queue
    {
        return DB::transaction(function () use ($user) {

            $queue = $this->getUserActiveQueue(
                $user,
                ['CALLED']
            );

            $queue->update([
                'status' => 'SERVING',
                'started_at' => now(),
            ]);

            $this->log(
                $queue,
                'STARTED',
                $user->id,
                "Pelayanan nomor {$queue->queue_number} dimulai di loket {$queue->counter->code}."
            );

            return $queue->fresh([
                'service',
                'counter',
                'handledBy',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | FINISH
    |--------------------------------------------------------------------------
    */

    /**
     * Menyelesaikan pelayanan.
     *
     * SERVING -> FINISHED
     */
    public function finish(User $user): Queue
    {
        return DB::transaction(function () use ($user) {

            $queue = $this->getUserActiveQueue(
                $user,
                ['SERVING']
            );

            $queue->update([
                'status' => 'FINISHED',
                'finished_at' => now(),
            ]);

            $this->log(
                $queue,
                'FINISHED',
                $user->id,
                "Nomor {$queue->queue_number} selesai dilayani di loket {$queue->counter->code}."
            );

            return $queue->fresh([
                'service',
                'counter',
                'handledBy',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | SKIP
    |--------------------------------------------------------------------------
    */

    /**
     * Melewati antrian.
     *
     * CALLED -> SKIPPED
     */
    public function skip(User $user): Queue
    {
        return DB::transaction(function () use ($user) {

            $queue = $this->getUserActiveQueue(
                $user,
                ['CALLED']
            );

            if (
                $queue->call_count <
                self::MAX_CALL_COUNT
            ) {
                throw new \RuntimeException(
                    'Antrian belum mencapai 3 kali pemanggilan.'
                );
            }

            $queue->update([
                'status' => 'SKIPPED',
            ]);

            $this->log(
                $queue,
                'SKIPPED',
                $user->id,
                "Nomor {$queue->queue_number} dilewati setelah {$queue->call_count} kali pemanggilan."
            );

            return $queue->fresh([
                'service',
                'counter',
                'handledBy',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | CANCEL
    |--------------------------------------------------------------------------
    */

    /**
     * Membatalkan antrian.
     *
     * WAITING -> CANCELLED
     *
     * SECURITY:
     *
     * Admin:
     *     boleh membatalkan semua service.
     *
     * Teller / CS:
     *     hanya boleh membatalkan queue
     *     dari service counter mereka.
     */
    public function cancel(
        User $user,
        int $queueId
    ): Queue {
        return DB::transaction(
            function () use ($user, $queueId) {

                $queue = Queue::query()
                    ->with([
                        'service',
                        'counter',
                        'handledBy',
                    ])
                    ->whereKey($queueId)
                    ->lockForUpdate()
                    ->first();

                if (!$queue) {
                    throw new \RuntimeException(
                        'Antrian tidak ditemukan.'
                    );
                }

                /*
                 * Hanya WAITING yang dapat dibatalkan.
                 */
                if ($queue->status !== 'WAITING') {
                    throw new \RuntimeException(
                        'Hanya antrian yang masih menunggu yang dapat dibatalkan.'
                    );
                }

                /*
                 * ADMIN
                 *
                 * Admin boleh membatalkan queue
                 * dari service mana pun.
                 */
                if ($user->hasRole('admin')) {

                    $queue->update([
                        'status' => 'CANCELLED',
                    ]);

                    $this->log(
                        $queue,
                        'CANCELLED',
                        $user->id,
                        "Nomor {$queue->queue_number} dibatalkan."
                    );

                    return $queue->fresh([
                        'service',
                        'counter',
                        'handledBy',
                    ]);
                }

                /*
                 * NON-ADMIN
                 *
                 * Ambil service dari counter user.
                 */
                $serviceId =
                    $user->counter?->service_id;

                if (!$serviceId) {
                    throw new \RuntimeException(
                        'Anda belum memiliki loket.'
                    );
                }

                /*
                 * SECURITY CHECK UTAMA
                 *
                 * Queue harus berasal dari
                 * service yang sama dengan counter user.
                 */
                if (
                    (int) $queue->service_id !==
                    (int) $serviceId
                ) {
                    throw new \RuntimeException(
                        'Anda tidak memiliki akses untuk membatalkan antrian layanan ini.'
                    );
                }

                /*
                 * Kalau lolos semua pengecekan,
                 * baru boleh dibatalkan.
                 */
                $queue->update([
                    'status' => 'CANCELLED',
                ]);

                $this->log(
                    $queue,
                    'CANCELLED',
                    $user->id,
                    "Nomor {$queue->queue_number} dibatalkan oleh {$user->name}."
                );

                return $queue->fresh([
                    'service',
                    'counter',
                    'handledBy',
                ]);
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVE COUNTER
    |--------------------------------------------------------------------------
    */

    /**
     * Mengambil counter aktif milik user.
     */
    private function getActiveCounter(
        User $user
    ): Counter {
        $counter = $user->counter()
            ->with('service')
            ->lockForUpdate()
            ->first();

        if (!$counter) {
            throw new \RuntimeException(
                'Anda belum memiliki loket.'
            );
        }

        if (!$counter->is_active) {
            throw new \RuntimeException(
                'Loket Anda sedang tidak aktif.'
            );
        }

        return $counter;
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVE QUEUE
    |--------------------------------------------------------------------------
    */

    /**
     * Mengambil queue aktif milik user.
     */
    private function getUserActiveQueue(
        User $user,
        array $statuses
    ): Queue {
        $counter = $this->getActiveCounter(
            $user
        );

        $queue = Queue::query()
            ->with([
                'service',
                'counter',
                'handledBy',
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
                $statuses
            )
            ->lockForUpdate()
            ->first();

        if (!$queue) {
            throw new \RuntimeException(
                'Tidak ada antrian yang dapat diproses.'
            );
        }

        return $queue;
    }

    /*
    |--------------------------------------------------------------------------
    | LOG
    |--------------------------------------------------------------------------
    */

    /**
     * Membuat audit log queue.
     */
    private function log(
        Queue $queue,
        string $action,
        ?int $userId = null,
        ?string $notes = null
    ): void {
        $queue->logs()->create([
            'user_id' => $userId,
            'action' => $action,
            'notes' => $notes,
        ]);
    }
}