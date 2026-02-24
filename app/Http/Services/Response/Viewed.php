<?php

namespace App\Http\Services\Response;

class Viewed
{
    protected static array $views = [
        'auth' => [
            'login' => 'auth.login',
            'forgot' => 'auth.forgot_password',
            'reset' => 'auth.reset_password',
        ],
        'slider' => [
            'list'  => 'admin.app.app_slider.index',
            'create' => 'admin.app.app_slider.create',
            'edit'   => 'admin.app.app_slider.edit',
        ],
        'user' => [
            'list'  => 'admin.user.index',
            'create' => 'admin.user.create',
            'profile' => 'admin.profile.index',
            'edit' => 'admin.profile.settings',
        ],
        'file' => [
            'list_data'  => 'admin.file_manager.list',
            'list'  => 'admin.file_manager.index',
            'create' => 'admin.file_manager.create',
            'partial_data' => 'admin.file_manager.file_data',
        ],
        'custom' => [
            'index'  => 'admin.custom_fields.index',
        ],
        'role' => [
            'list'  => 'admin.role.index',
            'create' => 'admin.role.create',
            'edit'   => 'admin.role.edit',
            'permission'   => 'admin.role.permissions',
            'permissionApi'   => 'admin.role.permissions_api',
            'apiList'   => 'admin.role.role_api',
        ],
        'settings' => [
            'index'  => 'admin.settings.index',
            'fields' => 'admin.settings.fields.index',
            'field' => 'admin.settings.fields.create',
            'field-edit' => 'admin.settings.fields.edit',
        ],
        'faqCategory' => [
            'list'  => 'admin.faq.category.index',
            'create' => 'admin.faq.category.create',
        ],
        'faq' => [
            'list'  => 'admin.faq.index',
            'create' => 'admin.faq.create',
        ],
        'postCategory' => [
            'list' => 'admin.post.category.index',
            'create' => 'admin.post.category.create',
        ],
        'tag' => [
            'list' => 'admin.post.tag.index',
            'create' => 'admin.post.tag.create',
        ],
        'post' => [
            'list' => 'admin.post.post.index',
            'create' => 'admin.post.post.create',
        ],
        'postComment' => [
            'list' => 'admin.post.comment.index',
            'reply' => 'admin.post.comment.reply',
        ],
    ];

    /**
     * Get a view path by group and key.
     */
    public static function get(string $group, string $key, ?string $default = null): ?string
    {
        return static::$views[$group][$key] ?? $default;
    }

    /**
     * Get all view paths (optional)
     */
    public static function all(): array
    {
        return static::$views;
    }
}
