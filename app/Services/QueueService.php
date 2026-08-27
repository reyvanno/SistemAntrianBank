<?php

namespace App\Services;

use App\Models\Queue;
use App\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class QueueService
{
    /**
     * Menampilkan daftar antrian untuk admin.
     */
    public function paginate(?string $search = null): LengthAwarePaginator
    {
        return Queue::query()
            ->with([
                'service',
                'counter',
                'handledBy',
            ])
            ->when($search, function ($query) use ($search) {
                $query->where(
                    'queue_number',
                    'ILIKE',
                    "%{$search}%"
                );
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * Membuat nomor antrian baru.
     */
    public function create(array $data): Queue
    {
        return DB::transaction(function () use ($data) {

            /*
             * Lock service terlebih dahulu.
             *
             * Tujuannya supaya dua customer yang mengambil
             * nomor pada service yang sama secara bersamaan
             * tidak mendapatkan nomor yang sama.
             */
            $service = Service::query()
                ->whereKey($data['service_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $prefix = $service->code;

            /*
             * Cari nomor terakhir pada hari ini
             * untuk service yang sama.
             */
            $lastQueue = Queue::query()
                ->whereDate('created_at', today())
                ->where('service_id', $service->id)
                ->orderByDesc('id')
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
                $prefix .
                str_pad(
                    $number,
                    3,
                    '0',
                    STR_PAD_LEFT
                );

            return Queue::create([
                'queue_number' => $queueNumber,
                'service_id' => $service->id,
                'status' => 'WAITING',
            ]);
        });
    }
}