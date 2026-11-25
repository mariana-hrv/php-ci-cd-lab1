<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'property_type' => $this->property_type,
            'city' => $this->city,
            'address' => $this->address,
            'area' => (float) $this->area,
            'rooms' => $this->rooms,

            'listings' => ListingResource::collection(
                $this->whenLoaded('listings')
            ),

            'photos' => PropertyPhotoResource::collection(
                $this->whenLoaded('photos')
            ),

            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
