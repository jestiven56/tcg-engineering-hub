<?php

namespace App\Policies;

use App\Models\Artifact;
use App\Models\User;

class ArtifactPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Artifact $artifact): bool
    {
        return true;
    }

    public function update(User $user, Artifact $artifact): bool
    {
        return $user->hasAnyRole(['admin', 'pm']);
    }

    public function updateStatus(User $user, Artifact $artifact): bool
    {
        return $user->hasAnyRole(['admin', 'pm']);
    }
}