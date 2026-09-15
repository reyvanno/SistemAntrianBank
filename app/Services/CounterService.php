<?php

namespace App\Services;

use App\Models\Counter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CounterService
{
    private const CACHE_KEY = 'master:counters:all';

    /**
     * Data counter untuk kebutuhan master/form.
     *
     * Hanya mengambil data yang bersifat master.
     */
    public function all(): Collection
    {
        return Cache::remember(
            self::CACHE_KEY,
            config('cache.master_data_ttl'),
            fn() => Counter::query()
                ->with('service:id,code,name')
                ->orderBy('code')
                ->get([
                    'id',
                    'service_id',
                    'code',
                    'name',
                    'is_active',
                ])
        );
    }

    /**
     * Hapus cache master counter.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Pagination counter untuk halaman admin.
     *
     * Tidak di-cache karena memiliki search dan pagination.
     */
    public function paginate(
        ?string $search = null
    ): LengthAwarePaginator {
        return Counter::query()
            ->with('service')
            ->when($search, function ($query) use ($search) {

                $query->where('code', 'ILIKE', "%{$search}%")
                    ->orWhere('name', 'ILIKE', "%{$search}%")
                    ->orWhereHas('service', function ($q) use ($search) {
                        $q->where(
                            'name',
                            'ILIKE',
                            "%{$search}%"
                        );
                    });

            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * Membuat counter baru.
     */
    public function create(array $data): Counter
    {
        $counter = Counter::create($data);

        $this->clearCache();

        return $counter;
    }

    /**
     * Memperbarui counter.
     */
    public function update(
        Counter $counter,
        array $data
    ): Counter {
        $counter->update($data);

        $this->clearCache();

        return $counter->refresh();
    }

    /**
     * Menghapus counter.
     */
    public function delete(Counter $counter): void
    {
        $counter->delete();

        $this->clearCache();
    }
}