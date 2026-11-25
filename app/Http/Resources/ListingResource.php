<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'price' => (float) $this->price,
            'description' => $this->description,
            'status' => $this->status,
            'seller_id' => $this->seller_id,

            'property' => $this->whenLoaded('property', fn () => [
                'id' => $this->property->id,
                'property_type' => $this->property->property_type,
                'city' => $this->property->city,
                'address' => $this->property->address,
                'area' => (float) $this->property->area,
                'rooms' => $this->property->rooms,
            ]),

            'seller' => $this->whenLoaded('seller', fn () => [
                'id' => $this->seller->id,
                'name' => $this->seller->name,
            ]),

            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
