<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $table = 'devices';

    protected $fillable = ['name', 'api_token', 'enabled'];

    protected $casts = [
        'enabled' => 'boolean',
    ];

}
