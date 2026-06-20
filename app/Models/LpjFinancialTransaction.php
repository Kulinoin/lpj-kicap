<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LpjFinancialTransaction extends Model
{
    public const CATEGORY_CONSUMPTION = 'Konsumsi';

    public const CATEGORY_ACCOMMODATION = 'Akomodasi';

    public const CATEGORY_OPERATIONAL = 'Operasional';

    public const CATEGORY_TRANSPORTATION = 'Transportasi';

    public const CATEGORY_DOCUMENTATION = 'Dokumentasi';

    public const CATEGORY_OTHER = 'Lainnya';

    public const TYPE_EXPENSE = 'expense';

    public const SOURCE_BALANCE = 'saldo_pegangan';

    public const SOURCE_ADVANCE = 'dana_talangan';

    public const STATUS_NEEDS_REVIEW = 'perlu_review';

    public const STATUS_WAITING_PROOF = 'menunggu_bukti';

    public const STATUS_VALID = 'valid';

    public const STATUS_REJECTED = 'ditolak';

    public const STATUS_NEEDS_REVISION = 'perlu_revisi';

    protected $fillable = [
        'lpj_id',
        'user_id',
        'type',
        'source_type',
        'status',
        'category',
        'description',
        'amount',
        'spent_at',
        'proof_path',
        'no_proof_reason',
        'admin_note',
        'reviewed_at',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'spent_at' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_NEEDS_REVIEW => 'Perlu Review',
            self::STATUS_WAITING_PROOF => 'Menunggu Bukti',
            self::STATUS_VALID => 'Valid',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_NEEDS_REVISION => 'Perlu Revisi',
        ];
    }

    public static function sourceLabels(): array
    {
        return [
            self::SOURCE_BALANCE => 'Saldo Pegangan',
            self::SOURCE_ADVANCE => 'Dana Talangan',
        ];
    }

    public static function categoryOptions(): array
    {
        return [
            self::CATEGORY_CONSUMPTION => self::CATEGORY_CONSUMPTION,
            self::CATEGORY_ACCOMMODATION => self::CATEGORY_ACCOMMODATION,
            self::CATEGORY_OPERATIONAL => self::CATEGORY_OPERATIONAL,
            self::CATEGORY_TRANSPORTATION => self::CATEGORY_TRANSPORTATION,
            self::CATEGORY_DOCUMENTATION => self::CATEGORY_DOCUMENTATION,
            self::CATEGORY_OTHER => self::CATEGORY_OTHER,
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function advanceClaim(): HasOne
    {
        return $this->hasOne(LpjAdvanceClaim::class, 'financial_transaction_id');
    }
}
