<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Counter extends Model
{
    protected $fillable = [
        'service_id',
        'code',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(
            Service::class
        );
    }

    public function queues(): HasMany
    {
        return $this->hasMany(
            Queue::class
        );
    }

    public function users(): HasMany
    {
        return $this->hasMany(
            User::class
        );
    }
}