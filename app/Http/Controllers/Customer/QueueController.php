<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreQueueRequest;
use App\Models\Queue;
use App\Models\Service;
use App\Services\QueueService;
use App\Services\ServiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class QueueController extends Controller
{
    public function __construct(
        protected QueueService $queueService,
        protected ServiceService $serviceService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create(): Response
    {
        return Inertia::render('Customer/Queue/Index', [
            'services' => $this->serviceService->all(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(
        StoreQueueRequest $request
    ): RedirectResponse {
        /*
         * Queue dibuat melalui QueueService.
         */
        $queue = $this->queueService->create(
            $request->validated()
        );

        /*
         * Kirim hasil queue ke halaman customer
         * melalui flash session.
         */
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
                        $queue->created_at
                                ?->timezone('Asia/Jakarta')
                            ->format('H:i:s'),
                ]
            );
    }

    /*
    |--------------------------------------------------------------------------
    | PDF
    |--------------------------------------------------------------------------
    */

    public function pdf(int $queue)
    {
        $queue = Queue::query()
            ->with('service')
            ->whereKey($queue)
            ->firstOrFail();

        $pdf = Pdf::loadView(
            'pdf.queue-ticket',
            [
                'queue' => $queue,
            ]
        );

        $pdf->setPaper('A5', 'portrait');

        return $pdf->download(
            "tiket-antrian-{$queue->queue_number}.pdf"
        );
    }
}