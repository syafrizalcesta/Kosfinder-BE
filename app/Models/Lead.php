<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Lead extends Model
{
    protected $primaryKey = 'leads_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'leads_id',
        'kos_id',
        'user_id',
    ];

    /**
     * Boot: auto-generate UUID untuk leads_id
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->leads_id)) {
                $model->leads_id = (string) Str::uuid();
            }
        });
    }

    // ── Relasi ────────────────────────────────────────────────────

    public function kos()
    {
        return $this->belongsTo(Kos::class, 'kos_id', 'kos_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}