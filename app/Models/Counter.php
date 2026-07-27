<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Counter extends Model
{
    protected $fillable = [
        'service_id',
        'code',
        'name',
        'is_active',
    ];
    
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function queues()
    {
        return $this->hasMany(Queue::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
