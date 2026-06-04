<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\UserGender;
use App\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Override;

use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

#[Fillable(['f_name', 'l_name', 'email', 'password', 'role', 'gender', 'date_of_birth'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'role' => UserRole::class,
            'gender' => UserGender::class
        ];
    }

    // ======= JWT Methods =======

    #[Override]
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    #[Override]
    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role
        ];
    }

    // ====== Relationships ======

    public function phones(): HasMany
    {
        return $this->hasMany(UserPhone::class);
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function staffDetail(): HasOne
    {
        return $this->hasOne(StaffDetail::class);
    }

    public function cart(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'staff_user_id');
    }

}
