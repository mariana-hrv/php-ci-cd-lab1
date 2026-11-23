<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViewingRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'listing_id',
        'preferred_date',
        'preferred_time',
        'status',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date:Y-m-d',
        ];
    }

    // Заявка належить одному покупцеві
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    // Заявка належить одному оголошенню
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
}
