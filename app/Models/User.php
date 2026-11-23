<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Користувач може створювати багато оголошень
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'seller_id');
    }

    // Користувач має багато записів у вибраному
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    // Зв'язок M:M із вибраними оголошеннями
    public function favoriteListings(): BelongsToMany
    {
        return $this->belongsToMany(
            Listing::class,
            'favorites',
            'user_id',
            'listing_id'
        )->withPivot('note')->withTimestamps();
    }

    // Користувач може подавати багато заявок
    public function viewingRequests(): HasMany
    {
        return $this->hasMany(ViewingRequest::class, 'buyer_id');
    }

    // Власні сповіщення системи нерухомості
    public function propertyNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    // Адміністратор може модерувати багато оголошень
    public function moderationRecords(): HasMany
    {
        return $this->hasMany(ModerationRecord::class, 'admin_id');
    }
}
