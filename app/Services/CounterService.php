<?php

namespace App\Services;

use App\Models\Counter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CounterService
{
    public function paginate(?string $search = null): LengthAwarePaginator
    {
        return Counter::query()
            ->with('service')
            ->when($search, function ($query) use ($search) {

                $query->where('code', 'ILIKE', "%{$search}%")
                    ->orWhere('name', 'ILIKE', "%{$search}%")
                    ->orWhereHas('service', function ($q) use ($search) {

                        $q->where('name', 'ILIKE', "%{$search}%");

                    });

            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function create(array $data): Counter
    {
        return Counter::create($data);
    }

    public function update(Counter $counter, array $data): Counter
    {
        $counter->update($data);

        return $counter->refresh();
    }

    public function delete(Counter $counter): void
    {
        $counter->delete();
    }
}
