<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // todos pueden ver la lista
    }

    public function view(User $user, Project $project): bool
    {
        return true; // todos pueden ver un proyecto
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'pm']);
    }

    public function update(User $user, Project $project): bool
    {
        return $user->hasAnyRole(['admin', 'pm']);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->hasAnyRole(['admin', 'pm']);
    }

    public function updateStatus(User $user, Project $project): bool
    {
        return $user->hasAnyRole(['admin', 'pm']);
    }
}