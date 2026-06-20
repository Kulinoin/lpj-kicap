<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityNote extends Model
{
    public const TYPE_RESULT = 'hasil';

    public const TYPE_EVALUATION = 'evaluasi';

    public const TYPE_OBSTACLE = 'kendala';

    public const TYPE_SUGGESTION = 'saran';

    protected $fillable = [
        'lpj_id',
        'user_id',
        'type',
        'content',
        'include_in_report',
    ];

    protected function casts(): array
    {
        return [
            'include_in_report' => 'boolean',
        ];
    }

    public function lpj(): BelongsTo
    {
        return $this->belongsTo(Lpj::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function types(): array
    {
        return [
            self::TYPE_RESULT,
            self::TYPE_EVALUATION,
            self::TYPE_OBSTACLE,
            self::TYPE_SUGGESTION,
        ];
    }

    public static function typeLabels(): array
    {
        return [
            self::TYPE_RESULT => 'Hasil di Lapangan',
            self::TYPE_EVALUATION => 'Evaluasi',
            self::TYPE_OBSTACLE => 'Kendala',
            self::TYPE_SUGGESTION => 'Saran Tindak Lanjut',
        ];
    }
}
