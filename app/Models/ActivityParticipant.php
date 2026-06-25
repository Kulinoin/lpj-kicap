<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityParticipant extends Model
{
    public const REGISTRATION_STATUS_UNREGISTERED = 'belum_registrasi';
    public const REGISTRATION_STATUS_REGISTERED = 'sudah_registrasi';

    public const SELECTION_STATUS_NOT_STARTED = 'belum_mulai';
    public const SELECTION_STATUS_ACTIVE = 'aktif';
    public const SELECTION_STATUS_ELIMINATED = 'gugur';
    public const SELECTION_STATUS_PASSED = 'lulus';
    public const SELECTION_STATUS_ABSENT = 'tidak_hadir';

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
    
        'whatsapp',
        'photo_path',
        'photo_disk',
        'photo_original_name',
        'photo_mime_type',
        'photo_size',
        'selection_registration_status',
        'selection_status',
        'registered_by',
        'registered_at',
        'current_selection_stage_id',
        'eliminated_selection_stage_id',
        'eliminated_selection_test_id',
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

    public function testResults(): HasMany
    {
        return $this->hasMany(ActivityParticipantTestResult::class);
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function currentSelectionStage(): BelongsTo
    {
        return $this->belongsTo(ActivitySelectionStage::class, 'current_selection_stage_id');
    }

    public function eliminatedStage(): BelongsTo
    {
        return $this->belongsTo(ActivitySelectionStage::class, 'eliminated_selection_stage_id');
    }

    public function eliminatedTest(): BelongsTo
    {
        return $this->belongsTo(ActivitySelectionTest::class, 'eliminated_selection_test_id');
    }

    public static function registrationStatusLabels(): array
    {
        return [
            self::REGISTRATION_STATUS_UNREGISTERED => 'Belum Registrasi',
            self::REGISTRATION_STATUS_REGISTERED => 'Sudah Registrasi',
        ];
    }

    public static function selectionStatusLabels(): array
    {
        return [
            self::SELECTION_STATUS_NOT_STARTED => 'Belum Mulai',
            self::SELECTION_STATUS_ACTIVE => 'Aktif / Masih Lanjut',
            self::SELECTION_STATUS_ELIMINATED => 'Gugur',
            self::SELECTION_STATUS_PASSED => 'Lulus',
            self::SELECTION_STATUS_ABSENT => 'Tidak Hadir',
        ];
    }
}
