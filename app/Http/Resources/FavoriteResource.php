<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'listing_id' => $this->listing_id,
            'note' => $this->note,

            'listing' => new ListingResource(
                $this->whenLoaded('listing')
            ),

            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
