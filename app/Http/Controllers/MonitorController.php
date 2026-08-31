<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Queue;
use Illuminate\Http\JsonResponse;

class MonitorController extends Controller
{
    /**
     * Menampilkan halaman monitor.
     */
    public function index()
    {
        return inertia('Monitor', $this->getMonitorData());
    }

    /**
     * Mengambil data monitor terbaru.
     *
     * Endpoint ini digunakan oleh Monitor
     * untuk melakukan pengecekan perubahan data
     * tanpa reload halaman penuh.
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
        $counters = Counter::query()
            ->with([
                'service',

                'queues' => function ($query) {
                    $query
                        ->whereIn('status', [
                            'CALLED',
                            'SERVING',
                        ])
                        ->latest()
                        ->limit(1);
                },
            ])
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $waitingQueues = Queue::query()
            ->with('service')
            ->where('status', 'WAITING')
            ->orderBy('id')
            ->get()
            ->groupBy(
                fn($queue) => $queue->service->name
            );

        /*
         * Ambil pemanggilan terbaru.
         *
         * Yang kita butuhkan bukan hanya queue_number,
         * tetapi juga called_at.
         *
         * called_at berubah setiap kali:
         *
         * CALL    -> called_at baru
         * RECALL  -> called_at baru
         *
         * Jadi Monitor bisa mengetahui bahwa
         * sebuah pemanggilan baru terjadi.
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
                    'id' => $latestCall->id,
                    'queue_number' => $latestCall->queue_number,
                    'service' => $latestCall->service?->name,
                    'counter' => $latestCall->counter?->code,
                    'call_count' => $latestCall->call_count,
                    'called_at' => $latestCall->called_at?->toISOString(),
                ]
                : null,
        ];
    }
}