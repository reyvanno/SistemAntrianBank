<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Services\ServiceService;
use App\Models\Service;
use App\Http\Requests\Admin\StoreServiceRequest;
use App\Http\Requests\Admin\UpdateServiceRequest;
use Illuminate\Database\QueryException;

class ServiceController extends BaseController
{
    public function __construct(
        protected ServiceService $serviceService
    ) {
        $this->middleware('permission:service.view')
            ->only('index');

        $this->middleware('permission:service.create')
            ->only(['create', 'store']);

        $this->middleware('permission:service.update')
            ->only(['edit', 'update']);

        $this->middleware('permission:service.delete')  
            ->only('destroy');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return inertia('Admin/Services/Index', [
            'services' => $this->serviceService->paginate(
                request('search')
            ),
            'filters' => [
                'search' => request('search'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia('Admin/Services/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreServiceRequest $request)
    {
        $this->serviceService->create(
            $request->validated()
        );

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Layanan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        return inertia('Admin/Services/Edit', [
            'service' => $service,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateServiceRequest $request,
        Service $service
    ) {
        $this->serviceService->update(
            $service,
            $request->validated()
        );

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Layanan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        try {

            $this->serviceService->delete($service);

            return redirect()
                ->back()
                ->with('success', 'Layanan berhasil dihapus.');
        } catch (QueryException $e) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Layanan tidak dapat dihapus karena masih digunakan.'
                );
        }
    }
}
