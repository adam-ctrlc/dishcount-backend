<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Role;
use App\Models\Purchase;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, SoftDeletes, HasFactory, Notifiable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(callback: function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'middle_name',
        'username',
        'email',
        'password',
        'role_id',
        'has_shop',
        'birth_date',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'profile_picture',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'has_shop' => 'boolean',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function shop()
    {
        return $this->hasOne(Shop::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
