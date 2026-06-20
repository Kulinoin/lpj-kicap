<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityCommittee extends Model
{
    protected $fillable = [
        'lpj_id',
        'created_by',
        'name',
        'role',
        'task',
        'contact',
    ];

    public function lpj(): BelongsTo
    {
        return $this->belongsTo(Lpj::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
