<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    protected $primaryKey = 'facility_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'facility_id',
        'facility_name',
        'icon_url',
        'category',
    ];
}
