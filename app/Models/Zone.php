<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Zone extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'is_active',
        'coordinates',
        'alias',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }
}
