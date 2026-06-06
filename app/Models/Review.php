<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Review extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'reviewid';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'reviewid',
        'user_id',
        'kos_id',
        'comment',
        'rating',
        'owner_reply',
        'owner_replied_at',
    ];

    protected $casts = [
        'rating'           => 'float',
        'owner_replied_at' => 'datetime',
    ];

    // Auto-generate UUID saat membuat review baru
    protected static function booted(): void
    {
        static::creating(function (Review $review) {
            if (empty($review->reviewid)) {
                $review->reviewid = (string) Str::uuid();
            }
        });
    }

    // ── Relasi ───────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function kos()
    {
        return $this->belongsTo(Kos::class, 'kos_id', 'kos_id');
    }

    public function photos()
    {
        return $this->hasMany(ReviewPhoto::class, 'reviewid', 'reviewid')
                    ->orderBy('sort_order');
    }
}