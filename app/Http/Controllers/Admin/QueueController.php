<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use App\Http\Requests\Admin\StoreQueueRequest;
use App\Services\QueueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class QueueController extends BaseController
{
    public function __construct(
        protected QueueService $queueService
    ) {
        $this->middleware('permission:queue.view')
            ->only([
                'index',
                'data',
            ]);

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

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $user = $request->user();

        $start = microtime(true);

        $queues = $this->queueService->paginate(
            $user,
            $request->input('search')
        );

        $paginateTime = microtime(true) - $start;

        $start = microtime(true);

        $services = $this->queueService->availableServices($user);

        $servicesTime = microtime(true) - $start;

        return inertia(
            'Admin/Queues/Index',
            [
                'queues' => $queues,

                'services' => $services,

                'filters' => [
                    'search' => $request->input('search'),
                ],
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | REALTIME DATA
    |--------------------------------------------------------------------------
    */

    /**
     * Mengambil data antrian terbaru.
     *
     * Endpoint ini dipanggil oleh Vue
     * setiap 1.5 detik.
     *
     * Tidak melakukan reload halaman.
     */
    public function data(Request $request): JsonResponse
    {
        $totalStart = microtime(true);

        $userStart = microtime(true);

        $user = $request->user();

        $userTime = microtime(true) - $userStart;

        $queueStart = microtime(true);

        $queues = $this->queueService->paginate(
            $user,
            $request->input('search')
        );

        $queueTime = microtime(true) - $queueStart;

        $responseStart = microtime(true);

        $response = response()->json([
            'queues' => $queues,
        ]);

        $responseTime = microtime(true) - $responseStart;

        $totalTime = microtime(true) - $totalStart;

        logger()->info('QUEUE DATA PERFORMANCE', [
            'user' => round($userTime * 1000, 2),
            'paginate' => round($queueTime * 1000, 2),
            'response' => round($responseTime * 1000, 2),
            'total' => round($totalTime * 1000, 2),
        ]);

        return $response;
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(
        StoreQueueRequest $request
    ): RedirectResponse {
        try {
            $queue = $this->queueService->createForUser(
                $request->user(),
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

    /*
    |--------------------------------------------------------------------------
    | CALL
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | RECALL
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | START
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | FINISH
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | SKIP
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | CANCEL
    |--------------------------------------------------------------------------
    */

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