<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'keycloak_sub',
        'name',
        'email',
        'phone',
        'password',
        'avatar_url',
        'membership_tier',
        'status',
        'last_login_at',
        'last_login_app',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->opoobo_id)) {
                $user->opoobo_id = (string) Str::uuid();
            }
        });
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getInitialsAttribute(): string
    {
        $parts = explode(' ', $this->name);
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= strtoupper(mb_substr($part, 0, 1));
        }
        return $initials;
    }

    public function moduleUsers()
    {
        return $this->hasMany(ModuleUser::class);
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function savedLocations()
    {
        return $this->hasMany(SavedLocation::class);
    }

    public function loginHistory()
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function flutterwaveAuthorizations()
    {
        return $this->hasMany(FlutterwaveAuthorization::class);
    }

    public function getLinkedModulesAttribute()
    {
        return $this->moduleUsers()
            ->where('is_active', true)
            ->get()
            ->map(fn ($mu) => [
                'module' => $mu->module_name,
                'module_uid' => $mu->module_uid,
                'linked_at' => $mu->linked_at?->toISOString(),
            ]);
    }
}
