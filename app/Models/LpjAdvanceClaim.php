<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpjAdvanceClaim extends Model
{
    public const STATUS_SUBMITTED = 'diajukan';

    public const STATUS_VERIFIED = 'diverifikasi';

    public const STATUS_REJECTED = 'ditolak';

    public const STATUS_PAID = 'dibayar';

    protected $fillable = [
        'lpj_id',
        'user_id',
        'financial_transaction_id',
        'amount',
        'status',
        'admin_note',
        'verified_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'verified_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_SUBMITTED => 'Diajukan',
            self::STATUS_VERIFIED => 'Diverifikasi',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_PAID => 'Dibayar',
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

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(LpjFinancialTransaction::class, 'financial_transaction_id');
    }
}
