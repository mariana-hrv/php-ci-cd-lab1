<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\ViewingRequest;

class ViewingRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ViewingRequest $viewingRequest): bool
    {
        return $user->id === $viewingRequest->buyer_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ViewingRequest $viewingRequest): bool
    {
        return $user->id === $viewingRequest->buyer_id;
    }

    public function delete(User $user, ViewingRequest $viewingRequest): bool
    {
        return $user->id === $viewingRequest->buyer_id;
    }

    public function incoming(User $user): bool
    {
        return true;
    }

    public function decide(User $user, ViewingRequest $viewingRequest): bool
    {
        return $user->id === $viewingRequest->listing->seller_id;
    }
}
