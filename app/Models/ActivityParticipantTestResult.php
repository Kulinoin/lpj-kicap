<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityParticipantTestResult extends Model
{
    public const STATUS_PENDING = 'belum_tes';
    public const STATUS_PASSED = 'lulus';
    public const STATUS_PASSED_WITH_NOTE = 'lulus_dengan_catatan';
    public const STATUS_FAILED = 'gagal';
    public const STATUS_ABSENT = 'tidak_hadir';

    protected $fillable = [
        'lpj_id',
        'activity_participant_id',
        'activity_selection_stage_id',
        'activity_selection_test_id',
        'created_by',
        'updated_by',
        'status',
        'result_value',
        'note',
        'file_path',
        'file_disk',
        'original_name',
        'mime_type',
        'file_size',
        'assessed_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'assessed_at' => 'datetime',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'Belum Tes',
            self::STATUS_PASSED => 'Lulus',
            self::STATUS_PASSED_WITH_NOTE => 'Lulus dengan Catatan',
            self::STATUS_FAILED => 'Gagal',
            self::STATUS_ABSENT => 'Tidak Hadir',
        ];
    }

    public function lpj(): BelongsTo
    {
        return $this->belongsTo(Lpj::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(ActivityParticipant::class, 'activity_participant_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(ActivitySelectionStage::class, 'activity_selection_stage_id');
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(ActivitySelectionTest::class, 'activity_selection_test_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}