<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_type',
        'city',
        'address',
        'area',
        'rooms',
    ];

    protected function casts(): array
    {
        return [
            'area' => 'decimal:2',
            'rooms' => 'integer',
        ];
    }

    // Один об'єкт нерухомості може мати багато оголошень
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    // Один об'єкт нерухомості може мати багато фотографій
    public function photos(): HasMany
    {
        return $this->hasMany(PropertyPhoto::class);
    }
}
