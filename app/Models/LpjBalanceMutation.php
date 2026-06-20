<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpjBalanceMutation extends Model
{
    public const TYPE_FUND_IN = 'fund_in';

    public const TYPE_EXPENSE = 'expense';

    public const TYPE_TRANSFER_OUT = 'transfer_out';

    public const TYPE_TRANSFER_IN = 'transfer_in';

    public const DIRECTION_IN = 'in';

    public const DIRECTION_OUT = 'out';

    protected $fillable = [
        'lpj_id',
        'user_id',
        'related_user_id',
        'financial_transaction_id',
        'created_by',
        'type',
        'direction',
        'amount',
        'balance_after',
        'transfer_reference',
        'note',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'occurred_at' => 'datetime',
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

    public function relatedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(LpjFinancialTransaction::class, 'financial_transaction_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
