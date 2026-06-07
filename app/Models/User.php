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

    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'user_name',
        'email',
        'password_hash',
        'phone_whatsapp',
        'role',
        'auth_provider',
        'is_active',
        'avatar_path',          // ← BARU: path foto profil
        'ktp_image_path',
        'selfie_image_path',
        'verification_status'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = 'USR-' . Str::random(10);
            }
        });
    }

    /**
     * Accessor: mengembalikan URL publik foto profil user.
     * Diakses sebagai $user->avatar_url dari response JSON.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path ?? null;
    }

    /**
     * Pastikan avatar_url ikut disertakan saat model di-serialize ke JSON.
     */
    protected $appends = ['avatar_url'];
}