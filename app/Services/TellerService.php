<?php

namespace App\Services;

use App\Models\Queue;
use App\Models\User;

class TellerService
{
    public function dashboard(User $user): array
    {
        $counter = $user->counter;

        if (!$counter) {
            return [
                'counter' => null,
                'currentQueue' => null,
                'waitingQueues' => collect(),
            ];
        }

        $currentQueue = Queue::query()
            ->with('service')
            ->where('counter_id', $counter->id)
            ->whereIn('status', [
                'CALLED',
                'SERVING',
            ])
            ->latest()
            ->first();

        $waitingQueues = Queue::query()
            ->with('service')
            ->where('service_id', $counter->service_id)
            ->where('status', 'WAITING')
            ->oldest()
            ->get();

        return [
            'counter' => $counter,
            'currentQueue' => $currentQueue,
            'waitingQueues' => $waitingQueues,
        ];
    }

    public function callNext(User $user): ?Queue
    {
        $counter = $user->counter;

        $queue = Queue::query()
            ->where('service_id', $counter->service_id)
            ->where('status', 'WAITING')
            ->oldest()
            ->first();

        if (!$queue) {
            return null;
        }

        $queue->update([
            'counter_id' => $counter->id,
            'handled_by' => $user->id,
            'status' => 'CALLED',
            'called_at' => now(),
        ]);

        return $queue->fresh();
    }

    public function startService(User $user): ?Queue
    {
        $queue = Queue::query()
            ->where('handled_by', $user->id)
            ->where('status', 'CALLED')
            ->latest()
            ->first();

        if (!$queue) {
            return null;
        }

        $queue->update([
            'status' => 'SERVING',
            'started_at' => now(),
        ]);

        return $queue->fresh();
    }

    public function finishService(User $user): ?Queue
    {
        $queue = Queue::query()
            ->where('handled_by', $user->id)
            ->where('status', 'SERVING')
            ->latest()
            ->first();

        if (!$queue) {
            return null;
        }

        $queue->update([
            'status' => 'FINISHED',
            'finished_at' => now(),
        ]);

        return $queue->fresh();
    }
}