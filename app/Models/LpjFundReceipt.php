<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpjFundReceipt extends Model
{
    public const SOURCE_INSTITUTION = 'Lembaga';

    public const SOURCE_SPONSOR = 'Sponsor';

    public const SOURCE_GOVERNMENT = 'Dinas';

    protected $fillable = [
        'lpj_id',
        'recorded_by',
        'source_name',
        'description',
        'amount',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'received_at' => 'date',
        ];
    }

    public function lpj(): BelongsTo
    {
        return $this->belongsTo(Lpj::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public static function sourceOptions(): array
    {
        return [
            self::SOURCE_INSTITUTION => self::SOURCE_INSTITUTION,
            self::SOURCE_SPONSOR => self::SOURCE_SPONSOR,
            self::SOURCE_GOVERNMENT => self::SOURCE_GOVERNMENT,
        ];
    }
}
