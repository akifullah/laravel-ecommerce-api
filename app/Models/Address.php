<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        "user_id",
        'label',
        'first_name',
        'last_name',
        'company',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country_code',
        'phone',
        'is_default_shipping',
        'is_default_billing',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'is_default_shipping' => "boolean",
        'is_default_billing' => "boolean",
        'latitude' => "decimal:7",
        'longitude' => "decimal:7"
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
