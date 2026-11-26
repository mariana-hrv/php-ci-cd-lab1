<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Listing;

class ListingService
{
    public function create(array $data, int $sellerId): Listing
    {
        $listing = Listing::create([
            ...$data,
            'seller_id' => $sellerId,
        ]);

        return $listing->refresh();
    }

    public function update(Listing $listing, array $data): Listing
    {
        $listing->update($data);

        return $listing;
    }

    public function delete(Listing $listing): void
    {
        $listing->delete();
    }
}
