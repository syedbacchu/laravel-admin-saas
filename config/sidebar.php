<?php


return [
    [
        'key' => 'dashboard',
        'label' => 'Dashboard',
        'route' => 'dashboard',
        'icon' => 'dashboard',
        'permission' => null,
    ],
    [
        'key' => 'users',
        'label' => 'User Management',
        'icon' => 'users',
        'permission' => null,
        'children' => [
            [
                'label' => 'User List',
                'route' => 'user.list',
                'permission' => 'user.list',
            ],
            [
                'label' => 'User Create',
                'route' => 'user.create',
                'permission' => 'user.create',
            ],
        ],
    ],
    [
        'key' => 'role',
        'label' => 'Role Management',
        'icon' => 'shield',
        'permission' => null,
        'children' => [
            [
                'label' => 'Web Role',
                'route' => 'role.index',
                'permission' => 'role.index',
            ],
            [
                'label' => 'Web Permissions',
                'route' => 'role.webPermission',
                'permission' => 'role.webPermission',
            ],
            [
                'label' => 'Api Role',
                'route' => 'role.apiRole',
                'permission' => 'role.apiRole',
            ],
            [
                'label' => 'Api Permissions',
                'route' => 'role.apiPermission',
                'permission' => 'role.apiPermission',
            ],
        ],
    ],
    [
        'key' => 'tenants',
        'label' => 'Tenants',
        'icon' => 'building',
        'permission' => null,
        'children' => [
            [
                'label' => 'Tenant List',
                'route' => 'tenant.list',
                'permission' => 'tenant.list',
            ],
            [
                'label' => 'Create Tenant',
                'route' => 'tenant.create',
                'permission' => 'tenant.create',
            ],
        ],
    ],
    [
        'key' => 'billing',
        'label' => 'Billing',
        'icon' => 'credit-card',
        'permission' => null,
        'children' => [
            [
                'label' => 'Feature List',
                'route' => 'feature.list',
                'permission' => 'feature.list',
            ],
            [
                'label' => 'Create Feature',
                'route' => 'feature.create',
                'permission' => 'feature.create',
            ],
            [
                'label' => 'Plan List',
                'route' => 'plan.list',
                'permission' => 'plan.list',
            ],
            [
                'label' => 'Create Plan',
                'route' => 'plan.create',
                'permission' => 'plan.create',
            ],
            [
                'label' => 'Subscription List',
                'route' => 'subscription.list',
                'permission' => 'subscription.list',
            ],
            [
                'label' => 'Create Subscription',
                'route' => 'subscription.create',
                'permission' => 'subscription.create',
            ],
            [
                'label' => 'Payment Method List',
                'route' => 'paymentMethod.list',
                'permission' => 'paymentMethod.list',
            ],
            [
                'label' => 'Create Payment Method',
                'route' => 'paymentMethod.create',
                'permission' => 'paymentMethod.create',
            ],
            [
                'label' => 'Subscription Payment List',
                'route' => 'subscriptionPayment.list',
                'permission' => 'subscriptionPayment.list',
            ],
            [
                'label' => 'Create Subscription Payment',
                'route' => 'subscriptionPayment.create',
                'permission' => 'subscriptionPayment.create',
            ],
            [
                'label' => 'Payment Report',
                'route' => 'subscriptionPayment.report',
                'permission' => 'subscriptionPayment.report',
            ],
        ],
    ],
    [
        'key' => 'app',
        'label' => 'App Setup',
        'icon' => 'app',
        'permission' => null,
        'children' => [
            [
                'label' => 'Slider',
                'route' => 'appSlider.list',
                'permission' => 'appSlider.list',
            ],
            [
                'label' => 'Slider Create',
                'route' => 'appSlider.create',
                'permission' => 'appSlider.create',
            ],
        ],
    ],
    [
        'key' => 'faq',
        'label' => 'FAQ',
        'icon' => 'faq',
        'permission' => null,
        'children' => [
            [
                'label' => 'Category',
                'route' => 'faqCategory.list',
                'permission' => 'faqCategory.list',
            ],
            [
                'label' => 'FAQ',
                'route' => 'faq.list',
                'permission' => 'faq.list',
            ],
        ],
    ],
    [
        'key' => 'blog',
        'label' => 'Blog',
        'icon' => 'newspaper',
        'permission' => null,
        'children' => [
            [
                'label' => 'Post Category',
                'route' => 'postCategory.list',
                'permission' => 'postCategory.list',
            ],
            [
                'label' => 'Create Category',
                'route' => 'postCategory.create',
                'permission' => 'postCategory.create',
            ],
            [
                'label' => 'Tag List',
                'route' => 'tag.list',
                'permission' => 'tag.list',
            ],
            [
                'label' => 'Create Tag',
                'route' => 'tag.create',
                'permission' => 'tag.create',
            ],
            [
                'label' => 'Post List',
                'route' => 'post.list',
                'permission' => 'post.list',
            ],
            [
                'label' => 'Create Post',
                'route' => 'post.create',
                'permission' => 'post.create',
            ],
            [
                'label' => 'Comments',
                'route' => 'postComment.list',
                'permission' => 'postComment.list',
            ],
        ],
    ],
    [
        'key' => 'page_builder',
        'label' => 'Page Builder',
        'icon' => 'page-builder',
        'permission' => null,
        'children' => [
            [
                'label' => 'Components',
                'route' => 'component.list',
                'permission' => 'component.list',
            ],

            [
                'label' => 'Pages',
                'route' => 'pages.index',
                'permission' => 'pages.index',
            ],

        ],
    ],
    [
        'key' => 'file_manager',
        'label' => 'File Manager',
        'route' => 'fileManager.list',
        'icon' => 'file-manager',
        'permission' => 'fileManager.list',
    ],
    [
        'key' => 'custom_fields',
        'label' => 'Custom Fields',
        'route' => 'customField.index',
        'icon' => 'custom-fields',
        'permission' => 'customField.index',
    ],

    [
        'key' => 'settings',
        'label' => 'Settings',
        'icon' => 'settings',
        'permission' => null,
        'children' => [
            [
                'label' => 'General Settings',
                'route' => 'settings.generalSetting',
                'permission' => 'settings.generalSetting',
            ],
            [
                'label' => 'Settings Fields',
                'route' => 'settings.fields.index',
                'permission' => 'settings.fields.index',
            ],
            [
                'label' => 'Language List',
                'route' => 'language.list',
                'permission' => 'language.list',
            ],
            [
                'label' => 'Newsletter Subscribers',
                'route' => 'subscriber.index',
                'permission' => null,
            ],
            [
                'label' => 'Testimonials List',
                'route' => 'testimonial.list',
                'permission' => 'testimonial.list',
            ],
            [
                'label' => 'Contact Messages',
                'route' => 'contact.index',
                'permission' => null,
            ],
            [
                'label' => 'Division List',
                'route' => 'division.index',
                'permission' => 'division.index',
            ],
            [
                'label' => 'District List',
                'route' => 'district.index',
                'permission' => 'district.index',
            ],
            [
                'label' => 'Thana List',
                'route' => 'thana.index',
                'permission' => 'thana.index',
            ],
            [
                'label' => 'Database Backups',
                'route' => 'databaseBackup.list',
                'permission' => 'databaseBackup.list',
            ],
        ],
    ],
    [
        'key' => 'audit',
        'label' => 'Audit Logs',
        'icon' => 'audit',
        'permission' => null,
        'children' => [
            [
                'label' => 'Logs',
                'route' => 'audit.logs',
                'permission' => 'audit.logs',
            ],
            [
                'label' => 'Settings',
                'route' => 'audit.settings',
                'permission' => 'audit.settings',
            ],
        ],
    ],
    [
        'key' => 'logs',
        'label' => 'Error Logs',
        'route' => 'errorLog',
        'icon' => 'logs',
        'permission' => 'errorLog',
    ],
];
