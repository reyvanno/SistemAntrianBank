<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQueueRequest;
use App\Models\Service;
use App\Services\QueueService;

class QueueController extends Controller
{
    public function __construct(
        protected QueueService $queueService
    ) {
    }

    public function index()
    {
        return inertia(
            'Admin/Queues/Index',
            [

                'queues' => $this->queueService
                    ->paginate(
                        request('search')
                    ),

                'services' => Service::all(),

                'filters' => [

                    'search' => request('search'),

                ],

            ]
        );
    }

    public function store(
        StoreQueueRequest $request
    ) {

        $queue = $this->queueService
            ->create(
                $request->validated()
            );

        return redirect()

            ->back()

            ->with(
                'success',
                "Nomor {$queue->queue_number} berhasil dibuat."
            );

    }
}