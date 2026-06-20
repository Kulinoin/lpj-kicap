<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpjUserBalance extends Model
{
    protected $fillable = [
        'lpj_id',
        'user_id',
        'balance',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
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
}
