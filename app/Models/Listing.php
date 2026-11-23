<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Listing extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'seller_id',
        'price',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    // Оголошення належить одному об'єкту нерухомості
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    // Оголошення належить одному продавцю
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    // Оголошення має багато записів у вибраному
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    // Зв'язок M:M між оголошеннями та користувачами
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'favorites',
            'listing_id',
            'user_id'
        )->withPivot('note')->withTimestamps();
    }

    // Оголошення має багато заявок на перегляд
    public function viewingRequests(): HasMany
    {
        return $this->hasMany(ViewingRequest::class);
    }

    // Оголошення має багато записів модерації
    public function moderationRecords(): HasMany
    {
        return $this->hasMany(ModerationRecord::class);
    }
}
