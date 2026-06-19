<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpjAssignedUser extends Model
{
    protected $fillable = [
        'lpj_id',
        'user_id',
        'role_label',
        'can_input_transaction',
        'can_upload_documentation',
        'can_edit_activity_data',
    ];

    protected function casts(): array
    {
        return [
            'can_input_transaction' => 'boolean',
            'can_upload_documentation' => 'boolean',
            'can_edit_activity_data' => 'boolean',
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
