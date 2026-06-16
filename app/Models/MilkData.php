<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MilkData extends Model
{
    use HasFactory;

    protected $table = 'milk_data';

    protected $fillable = [
        'data',
    ];
}
