<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        //
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // If role_id changed, clear old and new role sidebar cache
        if ($user->wasChanged('role_id')) {
            $oldRoleId = $user->getOriginal('role_id') ?? 'no_role';
            $newRoleId = $user->role_id ?? 'no_role';

            cache()->forget('sidebar_menu_role_' . $oldRoleId);
            cache()->forget('sidebar_menu_role_' . $newRoleId);
            cache()->forget('user_permissions_' . $user->id);
        }

        // Always clear user's cached permissions
        cache()->forget('user_permissions_' . $user->id);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        cache()->forget('user_permissions_' . $user->id);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
