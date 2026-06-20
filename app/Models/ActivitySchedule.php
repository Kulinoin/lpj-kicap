<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivitySchedule extends Model
{
    protected $fillable = [
        'lpj_id',
        'created_by',
        'start_time',
        'end_time',
        'activity_name',
        'responsible_person',
        'note',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function lpj(): BelongsTo
    {
        return $this->belongsTo(Lpj::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
