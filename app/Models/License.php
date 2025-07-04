<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    protected $table = 'license';

    protected $fillable = [
        'pin',
        'sport',
        'duration',
        'date_register',
        'date_use',
        'description_use',
        'user_name',
        'status'
    ];

    public $timestamps = false;
}
