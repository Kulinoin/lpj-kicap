<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivitySelectionStage extends Model
{
    protected $fillable = [
        'lpj_id',
        'name',
        'slug',
        'sort_order',
        'is_elimination',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_elimination' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function lpj(): BelongsTo
    {
        return $this->belongsTo(Lpj::class);
    }

    public function tests(): HasMany
    {
        return $this->hasMany(ActivitySelectionTest::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(ActivityParticipantTestResult::class);
    }
}