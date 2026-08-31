<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use App\Http\Requests\Admin\StoreQueueRequest;
use App\Models\Service;
use App\Services\QueueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

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

        $this->middleware('permission:queue.recall')
            ->only('recall');

        $this->middleware('permission:queue.start')
            ->only('start');

        $this->middleware('permission:queue.finish')
            ->only('finish');

        $this->middleware('permission:queue.skip')
            ->only('skip');

        $this->middleware('permission:queue.cancel')
            ->only('cancel');
    }

    public function index()
    {
        return inertia(
            'Admin/Queues/Index',
            [
                'queues' => $this->queueService->paginate(
                    request('search')
                ),

                'services' => Service::query()
                    ->orderBy('name')
                    ->get([
                        'id',
                        'code',
                        'name',
                    ]),

                'filters' => [
                    'search' => request('search'),
                ],
            ]
        );
    }

    public function store(
        StoreQueueRequest $request
    ): RedirectResponse {
        try {
            $queue = $this->queueService->create(
                $request->validated()
            );

            return redirect()
                ->back()
                ->with(
                    'success',
                    "Nomor {$queue->queue_number} berhasil dibuat."
                );
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    public function call(
        Request $request
    ): RedirectResponse {
        try {
            $queue = $this->queueService->callNext(
                $request->user()
            );

            return redirect()
                ->back()
                ->with(
                    'success',
                    "Nomor {$queue->queue_number} dipanggil."
                );
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    public function recall(
        Request $request
    ): RedirectResponse {
        try {
            $queue = $this->queueService->recall(
                $request->user()
            );

            return redirect()
                ->back()
                ->with(
                    'success',
                    "Nomor {$queue->queue_number} dipanggil kembali. Panggilan ke-{$queue->call_count}."
                );
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    public function start(
        Request $request
    ): RedirectResponse {
        try {
            $queue = $this->queueService->start(
                $request->user()
            );

            return redirect()
                ->back()
                ->with(
                    'success',
                    "Pelayanan nomor {$queue->queue_number} dimulai."
                );
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    public function finish(
        Request $request
    ): RedirectResponse {
        try {
            $queue = $this->queueService->finish(
                $request->user()
            );

            return redirect()
                ->back()
                ->with(
                    'success',
                    "Nomor {$queue->queue_number} selesai dilayani."
                );
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    public function skip(
        Request $request
    ): RedirectResponse {
        try {
            $queue = $this->queueService->skip(
                $request->user()
            );

            return redirect()
                ->back()
                ->with(
                    'success',
                    "Nomor {$queue->queue_number} dilewati."
                );
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    public function cancel(
        Request $request,
        int $queue
    ): RedirectResponse {
        try {
            $cancelledQueue = $this->queueService->cancel(
                $request->user(),
                $queue
            );

            return redirect()
                ->back()
                ->with(
                    'success',
                    "Nomor {$cancelledQueue->queue_number} berhasil dibatalkan."
                );
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }
}