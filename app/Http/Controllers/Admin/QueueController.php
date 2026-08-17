<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use App\Http\Requests\Admin\StoreQueueRequest;
use App\Models\Service;
use App\Services\QueueService;

class QueueController extends BaseController
{
    public function __construct(
        protected QueueService $queueService
    ) {
        $this->middleware('permission:queue.view')
            ->only('index');

        $this->middleware('permission:queue.create')
            ->only('store');
        
        $this->middleware('permission:queue.call')
            ->only('call');
        
        $this->middleware('permission:queue.start')
            ->only('start');

        $this->middleware('permission:queue.finish')
            ->only('finish');
        
        $this->middleware('permission:queue.cancel')
            ->only('cancel');
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