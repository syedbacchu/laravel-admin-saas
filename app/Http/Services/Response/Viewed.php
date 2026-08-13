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
        'language' => [
            'list' => 'admin.language.index',
            'create' => 'admin.language.create',
        ],
        'feature' => [
            'list' => 'admin.billing.feature.index',
            'create' => 'admin.billing.feature.create',
        ],
        'plan' => [
            'list' => 'admin.billing.plan.index',
            'create' => 'admin.billing.plan.create',
        ],
        'subscription' => [
            'list' => 'admin.billing.subscription.index',
            'create' => 'admin.billing.subscription.create',
            'update-plan' => 'admin.billing.subscription.update-plan',
        ],
        'paymentMethod' => [
            'list' => 'admin.billing.payment_method.index',
            'create' => 'admin.billing.payment_method.create',
        ],
        'subscriptionPayment' => [
            'list' => 'admin.billing.subscription_payment.index',
            'create' => 'admin.billing.subscription_payment.create',
            'report' => 'admin.billing.subscription_payment.report',
        ],
        'tenant' => [
            'list' => 'admin.tenant.index',
            'create' => 'admin.tenant.create',
            'edit' => 'admin.tenant.edit',
            'backups' => 'admin.tenant.backups',
            'logs' => 'admin.tenant.logs',
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
        'databaseBackup' => [
            'list' => 'admin.database_backup.list',
            'create' => 'admin.database_backup.create',
        ],
        'division' => [
            'list' => 'admin.division.index',
            'create' => 'admin.division.create',
        ],
        'district' => [
            'list' => 'admin.district.index',
            'create' => 'admin.district.create',
        ],
        'thana' => [
            'list' => 'admin.thana.index',
            'create' => 'admin.thana.create',
        ],
        'subscriber' => [
            'list' => 'admin.subscriber.list',
        ],
        'testimonial' => [
            'list' => 'admin.testimonial.index',
            'create' => 'admin.testimonial.create',
        ],
        'contact' => [
            'list' => 'admin.contact.list',
        ],
        'component' => [
            'list' => 'admin.component.list',
            'create' => 'admin.component.create',
            'fields' => 'admin.component.fields',
            'field_create' => 'admin.component.field_create',
            'field_show' => 'admin.component.field_show',
            'show' => 'admin.component.show',
        ],
        'page' => [
            'list' => 'admin.page.list',
            'create' => 'admin.page.create',
            'edit' => 'admin.page.edit',
            'show' => 'admin.page.show',
            'sections' => 'admin.page-section.list',
        ],
        'page-section' => [
            'create' => 'admin.page-section.create',
            'edit' => 'admin.page-section.edit',
            'list' => 'admin.page-section.list',
        ],
        'section-translation' => [
            'list' => 'admin.section-translation.list',
            'create' => 'admin.section-translation.create',
            'edit-content' => 'admin.section-translation.edit-content',
            'tabbed-edit' => 'admin.section-translation.tabbed-edit',
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
