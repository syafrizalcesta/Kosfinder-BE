<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KosView extends Model
{
    use HasFactory;

    protected $table = 'kos_views';
    
    // Gunakan bawaan Laravel karena id-nya adalah integer auto-increment
    protected $guarded = [];

    // Karena di migration kita hanya menggunakan created_at dan tidak ada updated_at
    const UPDATED_AT = null;

    // Relasi ke User (Bisa null jika tamu yang belum login membuka halaman)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Relasi ke Kos yang dilihat
    public function kos()
    {
        return $this->belongsTo(Kos::class, 'kos_id', 'kos_id');
    }
}