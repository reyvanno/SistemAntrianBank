<?php

namespace App\Services;

use App\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ServiceService
{
    private const CACHE_KEY = 'master:services:all';

    /**
     * Data service untuk kebutuhan master/form.
     *
     * Data ini disimpan di Redis selama TTL
     * yang ditentukan pada konfigurasi cache.
     */
    public function all(): Collection
    {
        return Cache::remember(
            self::CACHE_KEY,
            config('cache.master_data_ttl'),
            fn() => Service::query()
                ->orderBy('code')
                ->get([
                    'id',
                    'code',
                    'name',
                ])
        );
    }

    /**
     * Menghapus cache master service.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Pagination service untuk halaman admin.
     */
    public function paginate(
        ?string $search = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        return Service::query()
            ->when($search, function ($query) use ($search) {
                $query->where('code', 'ILIKE', "%{$search}%")
                    ->orWhere('name', 'ILIKE', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Membuat service baru.
     */
    public function create(array $data): Service
    {
        $service = Service::create($data);

        $this->clearCache();

        return $service;
    }

    /**
     * Memperbarui service.
     */
    public function update(
        Service $service,
        array $data
    ): Service {
        $service->update($data);

        $this->clearCache();

        return $service->refresh();
    }

    /**
     * Menghapus service.
     */
    public function delete(Service $service): void
    {
        $service->delete();

        $this->clearCache();
    }
}