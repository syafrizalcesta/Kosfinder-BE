<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Kos extends Model
{
    use HasFactory;

    protected $table = 'kos';
    
    // Konfigurasi Primary Key UUID (Sesuai ERD)
    protected $primaryKey = 'kos_id';
    public $incrementing = false;
    protected $keyType = 'string';

    // Mengizinkan semua kolom diisi secara massal (mempermudah proses Insert Data)
    protected $guarded = [];

    // Fungsi otomatis generate UUID saat Kos baru ditambahkan oleh pemilik
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    // ==========================================
    // RELASI KE TABEL LAIN (Sesuai ERD)
    // ==========================================

    // 1. Relasi ke tabel Users (Siapa pemilik kos ini?)
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'user_id');
    }

    // 2. Relasi Many-to-Many ke Facilities (Lewat tabel pivot kos_facilities)
    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'kos_facilities', 'kos_id', 'facilities_id');
    }

    // 3. Relasi Many-to-Many ke Rules (Lewat tabel pivot kos_rules)
    public function rules()
    {
        return $this->belongsToMany(Rule::class, 'kos_rules', 'kos_id', 'rules_id');
    }

    // 4. Relasi ke KosImages (Satu kos punya banyak gambar)
    public function images()
    {
        return $this->hasMany(KosImage::class, 'kos_id', 'kos_id');
    }

    // 4b. Relasi ke foto utama/thumbnail kos
    public function primaryImage()
    {
        return $this->hasOne(KosImage::class, 'kos_id', 'kos_id')->where('is_primary', true);
    }

    // 5. Relasi ke tabel Reviews (Ulasan untuk kos ini)
    public function reviews()
    {
        return $this->hasMany(Review::class, 'kos_id', 'kos_id');
    }

    // 6. Relasi ke Leads (Siapa saja yang klik WhatsApp kos ini)
    public function leads()
    {
        return $this->hasMany(Lead::class, 'kos_id', 'kos_id');
    }

    // 7. Relasi ke Wishlists (Siapa saja yang memfavoritkan kos ini)
    public function wishlited_by()
    {
        return $this->hasMany(Wishlist::class, 'kos_id', 'kos_id');
    }

    // 8. Relasi ke KosViews (Jumlah penonton/kunjungan)
    public function views()
    {
        return $this->hasMany(KosView::class, 'kos_id', 'kos_id');
    }
}