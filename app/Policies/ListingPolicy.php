<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Listing;
use App\Models\User;

class ListingPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Listing $listing): bool
    {
        return $listing->status === 'published'
            || $user?->isAdmin() === true
            || ($user !== null && $user->id === $listing->seller_id);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Listing $listing): bool
    {
        return $user->id === $listing->seller_id;
    }

    public function delete(User $user, Listing $listing): bool
    {
        return $user->id === $listing->seller_id;
    }
}
