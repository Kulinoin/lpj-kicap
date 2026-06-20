<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityDocumentation extends Model
{
    public const CATEGORY_LOCATION = 'lokasi';

    public const CATEGORY_PARTICIPANTS = 'peserta';

    public const CATEGORY_BRIEFING = 'briefing';

    public const CATEGORY_REGISTRATION = 'registrasi';

    public const CATEGORY_EXECUTION = 'pelaksanaan';

    public const CATEGORY_RESULT = 'hasil';

    public const CATEGORY_OTHER = 'lainnya';

    protected $fillable = [
        'lpj_id',
        'uploaded_by',
        'category',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'caption',
        'include_in_report',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'include_in_report' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function lpj(): BelongsTo
    {
        return $this->belongsTo(Lpj::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public static function categoryOptions(): array
    {
        return [
            self::CATEGORY_LOCATION => 'Lokasi',
            self::CATEGORY_PARTICIPANTS => 'Peserta',
            self::CATEGORY_BRIEFING => 'Briefing',
            self::CATEGORY_REGISTRATION => 'Registrasi',
            self::CATEGORY_EXECUTION => 'Pelaksanaan',
            self::CATEGORY_RESULT => 'Hasil',
            self::CATEGORY_OTHER => 'Lainnya',
        ];
    }
}
