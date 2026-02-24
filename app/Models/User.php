<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'phone_code',
        'password',
        'role_module',
        'role_id',
        'status',
        'is_private',
        'added_by',
        'is_phone_verified',
        'is_email_verified',
        'image',
        'gender',
        'date_of_birth',
        'blood_group',
        'language',
        'address',
        'country',
        'division',
        'district',
        'thana',
        'city',
        'postal_code',
        'is_social_login',
        'social_network_id',
        'social_network_type',
        'email_notification_status',
        'phone_notification_status',
        'push_notification_status',
        'facebook_link',
        'linkedin_link',
        'youtube_link',
        'twitter_link',
        'instagram_link',
        'whatsapp_link',
        'telegram_link',
        'device_token',
        'referral_code',
        'referred_by',
        'email_verified_at',
        'last_login_at',
        'last_login_ip'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function permissions()
    {
        // If user has a role, get its permissions; else return empty relation
        return $this->role()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id');
    }
    public function cachedPermissions(): array
    {
        return cache()->remember(
            'user_permissions_' . $this->id,
            3600,
            function () {
                if (!$this->role) {
                    return [];
                }
                return $this->role->permissions->pluck('slug')->toArray();
            }
        );
    }
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->cachedPermissions());
    }

}
