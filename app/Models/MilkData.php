<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MilkData extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'milk_data';

    protected $fillable = [
        'data',
    ];
}
