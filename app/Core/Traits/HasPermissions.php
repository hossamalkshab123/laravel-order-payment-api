<?php

namespace App\Core\Traits;

trait HasPermissions
{
    public function canAccessResource(mixed $resource, mixed $user): bool
    {
        return $resource->user_id === $user->id || $user->role === 'admin';
    }
}
