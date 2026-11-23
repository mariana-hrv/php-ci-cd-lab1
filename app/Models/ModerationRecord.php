<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModerationRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'admin_id',
        'decision',
        'comment',
    ];

    // Запис модерації належить одному оголошенню
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    // Запис модерації належить одному адміністратору
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
