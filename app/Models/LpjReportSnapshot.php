<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpjReportSnapshot extends Model
{
    public const SOURCE_ADMIN_PRINT = 'admin_print';

    public const SOURCE_ADMIN_PDF = 'admin_pdf';

    public const SOURCE_USER_PRINT = 'user_print';

    protected $fillable = [
        'lpj_id',
        'generated_by',
        'version',
        'snapshot_number',
        'source',
        'generated_by_role',
        'total_funds_received',
        'total_valid_expense',
        'total_remaining_fund',
        'snapshot_html',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'total_funds_received' => 'decimal:2',
            'total_valid_expense' => 'decimal:2',
            'total_remaining_fund' => 'decimal:2',
            'generated_at' => 'datetime',
        ];
    }

    public static function sourceLabels(): array
    {
        return [
            self::SOURCE_ADMIN_PRINT => 'Cetak Admin',
            self::SOURCE_ADMIN_PDF => 'Export PDF Admin',
            self::SOURCE_USER_PRINT => 'Cetak User',
        ];
    }

    public function lpj(): BelongsTo
    {
        return $this->belongsTo(Lpj::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
