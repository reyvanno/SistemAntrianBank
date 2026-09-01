<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreQueueRequest;
use App\Models\Service;
use App\Models\Queue;
use App\Services\QueueService;
use Illuminate\Http\RedirectResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;
use Inertia\Response;

class QueueController extends Controller
{
    public function __construct(
        protected QueueService $queueService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

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
         *
         * created_at akan mengikuti timezone
         * aplikasi Laravel, yaitu Asia/Jakarta.
         */
        $queue = $this->queueService->create(
            $request->validated()
        );

        /*
         * Kirim data queue ke halaman customer
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

                    /*
                     * Waktu pengambilan nomor.
                     *
                     * Karena APP_TIMEZONE sudah:
                     *
                     * Asia/Jakarta
                     *
                     * maka created_at akan diformat
                     * sesuai waktu Indonesia Barat.
                     */
                    'created_at' =>
                        $queue->created_at
                                ?->timezone('Asia/Jakarta')
                            ->format('H:i:s'),
                ]
            );
    }

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
