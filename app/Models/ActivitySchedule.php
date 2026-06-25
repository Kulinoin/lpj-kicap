<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivitySchedule extends Model
{
    public const STATUS_NOT_STARTED = 'belum_mulai';
    public const STATUS_IN_PROGRESS = 'sedang_berlangsung';
    public const STATUS_DONE = 'selesai';
    public const STATUS_BLOCKED = 'terkendala';

    protected $fillable = [
        'lpj_id',
        'created_by',
        'start_time',
        'end_time',
        'activity_name',
        'responsible_person',
        'note',
        'sort_order',
    
        'status',
        'status_note',
        'status_updated_by',
        'status_updated_at',
    
    ];

    protected function casts(): array
    {
        return [
            'status_updated_at' => 'datetime',
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
    public function statusUpdater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'status_updated_by');
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_NOT_STARTED => 'Belum mulai',
            self::STATUS_IN_PROGRESS => 'Sedang berlangsung',
            self::STATUS_DONE => 'Selesai',
            self::STATUS_BLOCKED => 'Terkendala',
        ];
    }

}
