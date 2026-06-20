<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityAttachment extends Model
{
    protected $fillable = [
        'lpj_id',
        'uploaded_by',
        'title',
        'file_path',
        'file_disk',
        'original_name',
        'mime_type',
        'file_size',
        'description',
        'include_in_report',
    ];

    protected function casts(): array
    {
        return [
            'include_in_report' => 'boolean',
        ];
    }

    public function lpj(): BelongsTo
    {
        return $this->belongsTo(Lpj::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
