<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationProfile extends Model
{
    protected $fillable = [
        'institution_name',
        'institution_type',
        'unit_name',
        'address',
        'phone',
        'mobile',
        'email',
        'website',
        'logo_path',
        'footer_text',
        'default_city',
        'leader_name',
        'leader_position',
    ];
}
