<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KosImage extends Model
{
    use HasFactory;

    // Custom primary key
    protected $primaryKey = 'image_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'image_id',
        'kos_id',
        'image_url',
        'is_primary',
    ];

    // Auto-generate image_id saat create
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = 'IMG-' . Str::random(10);
            }
        });
    }

    // Relasi: foto ini milik satu kos
    public function kos()
    {
        return $this->belongsTo(Kos::class, 'kos_id', 'kos_id');
    }
}
