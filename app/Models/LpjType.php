<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpjType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_external_event',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_external_event' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function lpjs(): HasMany
    {
        return $this->hasMany(Lpj::class);
    }

    public function narrativeTemplates(): HasMany
    {
        return $this->hasMany(NarrativeTemplate::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }
}
