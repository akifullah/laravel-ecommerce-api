<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        "username",
        'email',
        'password',
        'phone',
        'profile_photo_path',
        'locale',
        'timezone',
        'default_billing_address_id',
        'default_shipping_address_id',
        'preferences',
        'metadata',
        'is_active',
        'is_blocked',
        'wallet_balance',
        'loyality_points',
        'marketing_opt_in',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'gdpr_consented_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'preferences' => 'array',
            'metadata' => 'array',
            'wallet_balance' => 'decimal:2',
            'marketing_opt_in' => 'boolean',
            'is_active' => 'boolean',
            'is_blocked' => 'boolean',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted',
        ];
    }

    // MUTATOR TO AUTO HASH PASSWORDS
    public function setPasswordAttribute($value)
    {
        if ($value && ! Str::startsWith($value, '$2y$')) {
            $this->attributes['password'] = Hash::make($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }


    // Relations examples
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function defaultBillingAddress()
    {
        return $this->hasOne(Address::class)->where('is_default_billing', true);
    }

    public function defaultShippingAddress()
    {
        return $this->hasOne(Address::class)->where('is_default_shipping', true);
    }
}
