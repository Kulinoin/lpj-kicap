<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lpj extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_AKTIF = 'aktif';

    public const STATUS_FINISH = 'finish';

    public const STATUS_ARSIPKAN = 'arsipkan';

    public const COMPLETENESS_BELUM_LENGKAP = 'belum_lengkap';

    public const COMPLETENESS_SIAP_REVIEW = 'siap_review';

    public const COMPLETENESS_SIAP_FINALISASI = 'siap_finalisasi';

    protected $fillable = [
        'code',
        'title',
        'lpj_type_id',
        'created_by',
        'person_in_charge_id',
        'status',
        'completeness_status',
        'start_date',
        'end_date',
        'location',
        'funding_source',
        'assignment_letter_number',
        'period_label',
        'external_organizer',
        'organization_role',
        'total_funds_received',
        'total_valid_expense',
        'total_remaining_fund',
        'submitted_at',
        'approved_at',
        'approved_by',
        'finalized_at',
        'finalized_by',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'finalized_at' => 'datetime',
            'archived_at' => 'datetime',
            'total_funds_received' => 'decimal:2',
            'total_valid_expense' => 'decimal:2',
            'total_remaining_fund' => 'decimal:2',
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(LpjType::class, 'lpj_type_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function personInCharge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'person_in_charge_id');
    }

    public function assignedUsers(): HasMany
    {
        return $this->hasMany(LpjAssignedUser::class);
    }

    public function narratives(): HasMany
    {
        return $this->hasMany(LpjNarrative::class);
    }

    public function activityNotes(): HasMany
    {
        return $this->hasMany(ActivityNote::class);
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_AKTIF,
            self::STATUS_FINISH,
            self::STATUS_ARSIPKAN,
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'Draf',
            self::STATUS_AKTIF => 'Aktif',
            self::STATUS_FINISH => 'Selesai',
            self::STATUS_ARSIPKAN => 'Diarsipkan',
        ];
    }

    public static function userVisibleStatuses(): array
    {
        return [
            self::STATUS_AKTIF,
            self::STATUS_FINISH,
        ];
    }

    public function scopeVisibleToAssignedUser(Builder $query, User $user): Builder
    {
        return $query
            ->whereIn('status', self::userVisibleStatuses())
            ->whereHas('assignedUsers', fn (Builder $query): Builder => $query->where('user_id', $user->id));
    }
}
