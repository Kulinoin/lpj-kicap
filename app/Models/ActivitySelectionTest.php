<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivitySelectionTest extends Model
{
    protected $fillable = [
        'lpj_id',
        'activity_selection_stage_id',
        'name',
        'slug',
        'sort_order',
        'is_required',
        'is_elimination',
        'requires_reason_on_fail',
        'requires_attachment_on_fail',
        'allows_pass_with_note',
        'requires_attachment_on_pass_with_note',
        'result_label',
        'note_label',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_required' => 'boolean',
            'is_elimination' => 'boolean',
            'requires_reason_on_fail' => 'boolean',
            'requires_attachment_on_fail' => 'boolean',
            'allows_pass_with_note' => 'boolean',
            'requires_attachment_on_pass_with_note' => 'boolean',
        ];
    }

    public function lpj(): BelongsTo
    {
        return $this->belongsTo(Lpj::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(ActivitySelectionStage::class, 'activity_selection_stage_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(ActivityParticipantTestResult::class);
    }
}