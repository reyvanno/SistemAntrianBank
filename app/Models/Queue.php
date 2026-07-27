<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    protected $fillable = [
        'queue_number',
        'service_id',
        'counter_id',
        'handled_by',
        'status',
        'called_at',
        'started_at',
        'finished_at',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function counter()
    {
        return $this->belongsTo(Counter::class);
    }

    public function handledBy()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function logs()
    {
        return $this->hasMany(QueueLog::class);
    }
}
