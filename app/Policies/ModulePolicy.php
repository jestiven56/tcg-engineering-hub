<?php

namespace App\Policies;

use App\Models\Module;
use App\Models\User;

class ModulePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Module $module): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'pm', 'engineer']);
    }

    public function update(User $user, Module $module): bool
    {
        return $user->hasAnyRole(['admin', 'pm', 'engineer']);
    }

    public function delete(User $user, Module $module): bool
    {
        return $user->hasAnyRole(['admin', 'pm']);
    }

    public function validate(User $user, Module $module): bool
    {
        return $user->hasAnyRole(['admin', 'pm', 'engineer']);
    }
}