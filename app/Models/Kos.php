<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kos extends Model
{
    use HasFactory;

    // Beritahu Laravel nama tabel kita jika tidak jamak
    protected $table = 'kos';

    // Beritahu Laravel bahwa Primary Key kita adalah 'kos_id', BUKAN 'id'
    protected $primaryKey = 'kos_id';

    // Beritahu Laravel bahwa tipe Primary Key kita adalah String (Varchar), bukan Integer
    protected $keyType = 'string';

    // Beritahu Laravel bahwa Primary Key kita TIDAK auto-increment
    public $incrementing = false;

    // Izinkan pengisian data massal untuk kolom-kolom ini
    protected $guarded = []; 

    
}