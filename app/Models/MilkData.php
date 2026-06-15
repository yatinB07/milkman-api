<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MilkData extends Model
{
    protected $table = 'milk_data';

    protected $fillable = [
        'data',
    ];
}
