<?php

namespace App\Services;

use App\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ServiceService
{
    public function paginate(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return Service::query()
            ->when($search, function ($query) use ($search) {
                $query->where('code', 'ILIKE', "%{$search}%")
                    ->orWhere('name', 'ILIKE', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Service
    {
        return Service::create($data);
    }

    public function update(Service $service, array $data): Service
    {
        $service->update($data);

        return $service->refresh();
    }

    public function delete(Service $service): void
    {
        $service->delete();
    }
}
