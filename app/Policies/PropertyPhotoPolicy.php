<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Property;
use App\Models\PropertyPhoto;
use App\Models\User;

class PropertyPhotoPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, PropertyPhoto $photo): bool
    {
        return true;
    }

    public function create(User $user, Property $property): bool
    {
        return $property->listings()
            ->where('seller_id', $user->id)
            ->exists();
    }

    public function delete(User $user, PropertyPhoto $photo): bool
    {
        return $photo->property->listings()
            ->where('seller_id', $user->id)
            ->exists();
    }
}
