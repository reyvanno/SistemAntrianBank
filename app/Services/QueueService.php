<?php

namespace App\Services;

use App\Models\Queue;
use App\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class QueueService
{
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

    public function create(array $data): Queue
    {
        $service = Service::findOrFail(
            $data['service_id']
        );

        $prefix = $service->code;

        $lastQueue = Queue::query()

            ->whereDate(
                'created_at',
                today()
            )

            ->where(
                'service_id',
                $service->id
            )

            ->latest()

            ->first();

        $number = 1;

        if ($lastQueue) {

            $number = intval(
                substr(
                    $lastQueue->queue_number,
                    1
                )
            ) + 1;

        }

        return Queue::create([

            'queue_number' =>
                $prefix .
                str_pad(
                    $number,
                    3,
                    '0',
                    STR_PAD_LEFT
                ),

            'service_id' => $service->id,

            'status' => 'WAITING',

        ]);
    }
}