<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModerationRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'listing_id' => $this->listing_id,
            'admin_id' => $this->admin_id,
            'decision' => $this->decision,
            'comment' => $this->comment,

            'listing' => new ListingResource(
                $this->whenLoaded('listing')
            ),

            'admin' => $this->whenLoaded('admin', fn () => [
                'id' => $this->admin->id,
                'name' => $this->admin->name,
            ]),

            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
