<?php

namespace App\Http\Services\Auth;

use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AdminActivityLogger
{
    public function log(User $user, string $action, array $data = []): void
    {
        try {
            AdminActivityLog::create([
                'user_id' => $user->id,
                'action' => $action,
                'description' => $this->getActionDescription($action, $user),
                'data' => $data,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log admin activity', [
                'user_id' => $user->id,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function logFailedLogin(string $email, array $data = []): void
    {
        try {
            AdminActivityLog::create([
                'user_id' => null,
                'action' => 'failed_admin_login',
                'description' => "Failed admin login attempt for {$email}",
                'data' => array_merge($data, ['email' => $email]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log failed login attempt', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function getActionDescription(string $action, User $user): string
    {
        return match($action) {
            'admin_login' => "{$user->name} ({$user->user_type}) logged into admin panel",
            'admin_logout' => "{$user->name} ({$user->user_type}) logged out from admin panel",
            'user_created' => "{$user->name} created a new user",
            'user_updated' => "{$user->name} updated user information",
            'user_deleted' => "{$user->name} deleted a user",
            'settings_updated' => "{$user->name} updated system settings",
            default => "{$user->name} performed {$action}",
        };
    }
}
