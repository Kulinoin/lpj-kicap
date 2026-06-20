<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpjNarrative extends Model
{
    public const SECTION_BACKGROUND = 'background';

    public const SECTION_PURPOSE = 'purpose';

    public const SECTION_OBJECTIVE = 'objective';

    public const SECTION_EXECUTION = 'execution';

    public const SECTION_RESULT = 'result';

    public const SECTION_EVALUATION = 'evaluation';

    public const SECTION_CLOSING = 'closing';

    protected $fillable = [
        'lpj_id',
        'section',
        'content',
        'generated_from_template_id',
        'is_edited',
    ];

    protected function casts(): array
    {
        return [
            'is_edited' => 'boolean',
        ];
    }

    public function lpj(): BelongsTo
    {
        return $this->belongsTo(Lpj::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(NarrativeTemplate::class, 'generated_from_template_id');
    }

    public static function sections(): array
    {
        return [
            self::SECTION_BACKGROUND,
            self::SECTION_PURPOSE,
            self::SECTION_OBJECTIVE,
            self::SECTION_EXECUTION,
            self::SECTION_RESULT,
            self::SECTION_EVALUATION,
            self::SECTION_CLOSING,
        ];
    }

    public static function sectionLabels(): array
    {
        return [
            self::SECTION_BACKGROUND => 'Latar Belakang',
            self::SECTION_PURPOSE => 'Tujuan Kegiatan',
            self::SECTION_OBJECTIVE => 'Sasaran Kegiatan',
            self::SECTION_EXECUTION => 'Pelaksanaan',
            self::SECTION_RESULT => 'Hasil Kegiatan',
            self::SECTION_EVALUATION => 'Evaluasi dan Kendala',
            self::SECTION_CLOSING => 'Penutup',
        ];
    }
}
