<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    protected $primaryKey = 'rule_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'rule_id',
        'rule_name',
        'icon_url',
        'category',
    ];
}
