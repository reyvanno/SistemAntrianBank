<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use App\Http\Requests\Admin\StoreCounterRequest;
use App\Http\Requests\Admin\UpdateCounterRequest;
use App\Models\Counter;
use App\Models\Service;
use App\Services\CounterService;
use Illuminate\Database\QueryException;

class CounterController extends BaseController
{
    public function __construct(
        protected CounterService $counterService
    ) {
        $this->middleware('permission:counter.view')
            ->only('index');

        $this->middleware('permission:counter.create')
            ->only(['create', 'store']);

        $this->middleware('permission:counter.update')
            ->only(['edit', 'update']);

        $this->middleware('permission:counter.delete')
            ->only('destroy');
    }

    public function index()
    {
        return inertia('Admin/Counters/Index', [
            'counters' => $this->counterService->paginate(
                request('search')
            ),

            'filters' => [
                'search' => request('search'),
            ],
        ]);
    }

    public function create()
    {
        return inertia('Admin/Counters/Create', [
            'services' => Service::orderBy('name')->get(),
        ]);
    }

    public function store(StoreCounterRequest $request)
    {
        $this->counterService->create(
            $request->validated()
        );

        return redirect()
            ->route('admin.counters.index')
            ->with('success', 'Loket berhasil ditambahkan.');
    }

    public function edit(Counter $counter)
    {
        return inertia('Admin/Counters/Edit', [
            'counter' => $counter,
            'services' => Service::orderBy('name')->get(),
        ]);
    }

    public function update(
        UpdateCounterRequest $request,
        Counter $counter
    ) {

        $this->counterService->update(
            $counter,
            $request->validated()
        );

        return redirect()
            ->route('admin.counters.index')
            ->with('success', 'Loket berhasil diperbarui.');
    }

    public function destroy(Counter $counter)
    {
        try {

            $this->counterService->delete($counter);

            return redirect()
                ->back()
                ->with('success', 'Loket berhasil dihapus.');
        } catch (QueryException $e) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Loket tidak dapat dihapus karena masih digunakan.'
                );
        }
    }
}
