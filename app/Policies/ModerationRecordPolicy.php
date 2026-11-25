<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ModerationRecord;
use App\Models\User;

class ModerationRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, ModerationRecord $record): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }
}
