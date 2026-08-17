<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Queue;

class MonitorController extends Controller
{
    public function index()
    {
        $counters = Counter::with([
            'service',
            'queues' => function ($query) {
                $query->whereIn('status', ['CALLED', 'SERVING'])
                    ->latest()
                    ->limit(1);
            },
        ])
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $waitingQueues = Queue::with('service')
            ->where('status', 'WAITING')
            ->orderBy('id')
            ->get()
            ->groupBy(fn($queue) => $queue->service->name);

        return inertia('Monitor', [
            'counters' => $counters,
            'waitingQueues' => $waitingQueues,
        ]);
    }
}