<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReviewPhoto extends Model
{
    protected $primaryKey = 'photo_id';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'photo_id',
        'reviewid',
        'photo_url',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::creating(function (ReviewPhoto $photo) {
            if (empty($photo->photo_id)) {
                $photo->photo_id = (string) Str::uuid();
            }
        });
    }

    public function review()
    {
        return $this->belongsTo(Review::class, 'reviewid', 'reviewid');
    }
}