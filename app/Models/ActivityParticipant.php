<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityParticipant extends Model
{
    public const ATTENDANCE_PRESENT = 'hadir';

    public const ATTENDANCE_ABSENT = 'tidak_hadir';

    public const ATTENDANCE_PERMISSION = 'izin';

    protected $fillable = [
        'lpj_id',
        'created_by',
        'name',
        'origin',
        'participant_number',
        'attendance_status',
        'result_status',
        'note',
    ];

    public function lpj(): BelongsTo
    {
        return $this->belongsTo(Lpj::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function attendanceOptions(): array
    {
        return [
            self::ATTENDANCE_PRESENT => 'Hadir',
            self::ATTENDANCE_ABSENT => 'Tidak Hadir',
            self::ATTENDANCE_PERMISSION => 'Izin',
        ];
    }
}
