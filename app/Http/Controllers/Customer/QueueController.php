<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreQueueRequest;
use App\Models\Service;
use App\Services\QueueService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class QueueController extends Controller
{
    public function __construct(
        protected QueueService $queueService
    ) {
    }

    public function create(): Response
    {
        return Inertia::render(
            'Customer/Queue/Index',
            [
                'services' => Service::query()
                    ->orderBy('code')
                    ->get([
                        'id',
                        'code',
                        'name',
                    ]),
            ]
        );
    }

    public function store(
        StoreQueueRequest $request
    ): RedirectResponse {
        $queue = $this->queueService->create(
            $request->validated()
        );

        return redirect()
            ->route('customer.queue.create')
            ->with(
                'success',
                "Nomor {$queue->queue_number} berhasil dibuat."
            )
            ->with(
                'queue',
                [
                    'id' => $queue->id,

                    'queue_number' =>
                        $queue->queue_number,

                    'service' =>
                        $queue->service?->name,

                    'service_code' =>
                        $queue->service?->code,

                    'status' =>
                        $queue->status,

                    'created_at' =>
                        $queue->created_at?->format('H:i:s'),
                ]
            );
    }
}