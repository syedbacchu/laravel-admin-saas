<?php

namespace Database\Seeders;

use App\Models\Component;
use App\Models\ComponentField;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComponentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {

            $components = [

                /*
                |--------------------------------------------------------------------------
                | Slider
                |--------------------------------------------------------------------------
                */
                [
                    'name' => 'Slider',
                    'slug' => 'slider',
                    'description' => 'Responsive image slider with multiple slides.',
                    'fields' => [
                        [
                            'name' => 'slides',
                            'label' => 'Slides',
                            'field_type' => 'repeatable',
                            'is_required' => 1,
                            'is_translatable' => false,
                            'sort_order' => 1,
                            'config' => [
                                'min_items' => 1,
                                'max_items' => null,
                            ],
                            'children' => [
                                [
                                    'name' => 'image',
                                    'label' => 'Image',
                                    'field_type' => 'responsive_image',
                                    'is_required' => true,
                                    'is_translatable' => false,
                                    'sort_order' => 1,
                                ],
                                [
                                    'name' => 'title',
                                    'label' => 'Title',
                                    'field_type' => 'text',
                                    'is_required' => false,
                                    'is_translatable' => true,
                                    'sort_order' => 2,
                                ],
                                [
                                    'name' => 'heading',
                                    'label' => 'Heading',
                                    'field_type' => 'text',
                                    'is_required' => false,
                                    'is_translatable' => true,
                                    'sort_order' => 3,
                                ],
                                [
                                    'name' => 'description',
                                    'label' => 'Description',
                                    'field_type' => 'textarea',
                                    'is_required' => false,
                                    'is_translatable' => true,
                                    'sort_order' => 4,
                                ],
                                [
                                    'name' => 'button_text',
                                    'label' => 'Button Text',
                                    'field_type' => 'text',
                                    'is_required' => false,
                                    'is_translatable' => true,
                                    'sort_order' => 5,
                                ],
                                [
                                    'name' => 'button_link',
                                    'label' => 'Button Link',
                                    'field_type' => 'url',
                                    'is_required' => false,
                                    'is_translatable' => false,
                                    'sort_order' => 6,
                                ],
                            ],
                        ],
                    ],
                ],

                /*
                |--------------------------------------------------------------------------
                | Text Section
                |--------------------------------------------------------------------------
                */
                [
                    'name' => 'Text Section',
                    'slug' => 'text_section',
                    'description' => 'Simple text content section.',
                    'fields' => [
                        [
                            'name' => 'title',
                            'label' => 'Title',
                            'field_type' => 'text',
                            'is_required' => false,
                            'is_translatable' => true,
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'heading',
                            'label' => 'Heading',
                            'field_type' => 'text',
                            'is_required' => true,
                            'is_translatable' => true,
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'description',
                            'label' => 'Description',
                            'field_type' => 'richtext',
                            'is_required' => false,
                            'is_translatable' => true,
                            'sort_order' => 3,
                        ],
                        [
                            'name' => 'button_text',
                            'label' => 'Button Text',
                            'field_type' => 'text',
                            'is_required' => false,
                            'is_translatable' => true,
                            'sort_order' => 4,
                        ],
                        [
                            'name' => 'button_link',
                            'label' => 'Button Link',
                            'field_type' => 'url',
                            'is_required' => false,
                            'is_translatable' => false,
                            'sort_order' => 5,
                        ],
                    ],
                ],

                /*
                |--------------------------------------------------------------------------
                | Features
                |--------------------------------------------------------------------------
                */
                [
                    'name' => 'Features',
                    'slug' => 'features',
                    'description' => 'Feature cards section.',
                    'fields' => [
                        [
                            'name' => 'title',
                            'label' => 'Section Title',
                            'field_type' => 'text',
                            'is_required' => false,
                            'is_translatable' => true,
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'description',
                            'label' => 'Section Description',
                            'field_type' => 'textarea',
                            'is_required' => false,
                            'is_translatable' => true,
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'items',
                            'label' => 'Features',
                            'field_type' => 'repeatable',
                            'is_required' => true,
                            'is_translatable' => false,
                            'sort_order' => 3,
                            'config' => [
                                'min_items' => 1,
                                'max_items' => null,
                            ],
                            'children' => [
                                [
                                    'name' => 'icon',
                                    'label' => 'Icon',
                                    'field_type' => 'image',
                                    'is_required' => false,
                                    'is_translatable' => false,
                                    'sort_order' => 1,
                                ],
                                [
                                    'name' => 'title',
                                    'label' => 'Title',
                                    'field_type' => 'text',
                                    'is_required' => true,
                                    'is_translatable' => true,
                                    'sort_order' => 2,
                                ],
                                [
                                    'name' => 'description',
                                    'label' => 'Description',
                                    'field_type' => 'textarea',
                                    'is_required' => false,
                                    'is_translatable' => true,
                                    'sort_order' => 3,
                                ],
                                [
                                    'name' => 'link',
                                    'label' => 'Link',
                                    'field_type' => 'url',
                                    'is_required' => false,
                                    'is_translatable' => false,
                                    'sort_order' => 4,
                                ],
                            ],
                        ],
                    ],
                ],

                /*
                |--------------------------------------------------------------------------
                | Services
                |--------------------------------------------------------------------------
                */
                [
                    'name' => 'Services',
                    'slug' => 'services',
                    'description' => 'Services listing section.',
                    'fields' => [
                        [
                            'name' => 'title',
                            'label' => 'Section Title',
                            'field_type' => 'text',
                            'is_required' => false,
                            'is_translatable' => true,
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'heading',
                            'label' => 'Heading',
                            'field_type' => 'text',
                            'is_required' => false,
                            'is_translatable' => true,
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'description',
                            'label' => 'Description',
                            'field_type' => 'textarea',
                            'is_required' => false,
                            'is_translatable' => true,
                            'sort_order' => 3,
                        ],
                        [
                            'name' => 'items',
                            'label' => 'Services',
                            'field_type' => 'repeatable',
                            'is_required' => true,
                            'is_translatable' => false,
                            'sort_order' => 4,
                            'config' => [
                                'min_items' => 1,
                                'max_items' => null,
                            ],
                            'children' => [
                                [
                                    'name' => 'image',
                                    'label' => 'Image',
                                    'field_type' => 'responsive_image',
                                    'is_required' => false,
                                    'is_translatable' => false,
                                    'sort_order' => 1,
                                ],
                                [
                                    'name' => 'title',
                                    'label' => 'Title',
                                    'field_type' => 'text',
                                    'is_required' => true,
                                    'is_translatable' => true,
                                    'sort_order' => 2,
                                ],
                                [
                                    'name' => 'description',
                                    'label' => 'Description',
                                    'field_type' => 'textarea',
                                    'is_required' => false,
                                    'is_translatable' => true,
                                    'sort_order' => 3,
                                ],
                                [
                                    'name' => 'link',
                                    'label' => 'Link',
                                    'field_type' => 'url',
                                    'is_required' => false,
                                    'is_translatable' => false,
                                    'sort_order' => 4,
                                ],
                            ],
                        ],
                    ],
                ],

                /*
                |--------------------------------------------------------------------------
                | Gallery
                |--------------------------------------------------------------------------
                */
                [
                    'name' => 'Gallery',
                    'slug' => 'gallery',
                    'description' => 'Image gallery section.',
                    'fields' => [
                        [
                            'name' => 'title',
                            'label' => 'Section Title',
                            'field_type' => 'text',
                            'is_required' => false,
                            'is_translatable' => true,
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'description',
                            'label' => 'Description',
                            'field_type' => 'textarea',
                            'is_required' => false,
                            'is_translatable' => true,
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'items',
                            'label' => 'Gallery Items',
                            'field_type' => 'repeatable',
                            'is_required' => true,
                            'is_translatable' => false,
                            'sort_order' => 3,
                            'config' => [
                                'min_items' => 1,
                                'max_items' => null,
                            ],
                            'children' => [
                                [
                                    'name' => 'image',
                                    'label' => 'Image',
                                    'field_type' => 'responsive_image',
                                    'is_required' => true,
                                    'is_translatable' => false,
                                    'sort_order' => 1,
                                ],
                                [
                                    'name' => 'title',
                                    'label' => 'Title',
                                    'field_type' => 'text',
                                    'is_required' => false,
                                    'is_translatable' => true,
                                    'sort_order' => 2,
                                ],
                                [
                                    'name' => 'link',
                                    'label' => 'Link',
                                    'field_type' => 'url',
                                    'is_required' => false,
                                    'is_translatable' => false,
                                    'sort_order' => 3,
                                ],
                            ],
                        ],
                    ],
                ],

                /*
                |--------------------------------------------------------------------------
                | Testimonials
                |--------------------------------------------------------------------------
                */
                [
                    'name' => 'Testimonials',
                    'slug' => 'testimonials',
                    'description' => 'Customer testimonials section.',
                    'fields' => [
                        [
                            'name' => 'title',
                            'label' => 'Section Title',
                            'field_type' => 'text',
                            'is_required' => false,
                            'is_translatable' => true,
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'description',
                            'label' => 'Description',
                            'field_type' => 'textarea',
                            'is_required' => false,
                            'is_translatable' => true,
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'items',
                            'label' => 'Testimonials',
                            'field_type' => 'repeatable',
                            'is_required' => true,
                            'is_translatable' => false,
                            'sort_order' => 3,
                            'config' => [
                                'min_items' => 1,
                                'max_items' => null,
                            ],
                            'children' => [
                                [
                                    'name' => 'image',
                                    'label' => 'Customer Image',
                                    'field_type' => 'image',
                                    'is_required' => false,
                                    'is_translatable' => false,
                                    'sort_order' => 1,
                                ],
                                [
                                    'name' => 'name',
                                    'label' => 'Customer Name',
                                    'field_type' => 'text',
                                    'is_required' => true,
                                    'is_translatable' => true,
                                    'sort_order' => 2,
                                ],
                                [
                                    'name' => 'designation',
                                    'label' => 'Designation',
                                    'field_type' => 'text',
                                    'is_required' => false,
                                    'is_translatable' => true,
                                    'sort_order' => 3,
                                ],
                                [
                                    'name' => 'message',
                                    'label' => 'Message',
                                    'field_type' => 'textarea',
                                    'is_required' => true,
                                    'is_translatable' => true,
                                    'sort_order' => 4,
                                ],
                                [
                                    'name' => 'rating',
                                    'label' => 'Rating',
                                    'field_type' => 'number',
                                    'is_required' => false,
                                    'is_translatable' => false,
                                    'sort_order' => 5,
                                    'config' => [
                                        'min' => 1,
                                        'max' => 5,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],

                /*
                |--------------------------------------------------------------------------
                | Contact
                |--------------------------------------------------------------------------
                */
                [
                    'name' => 'Contact',
                    'slug' => 'contact',
                    'description' => 'Contact information and form section.',
                    'fields' => [
                        [
                            'name' => 'title',
                            'label' => 'Title',
                            'field_type' => 'text',
                            'is_required' => false,
                            'is_translatable' => true,
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'heading',
                            'label' => 'Heading',
                            'field_type' => 'text',
                            'is_required' => true,
                            'is_translatable' => true,
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'description',
                            'label' => 'Description',
                            'field_type' => 'textarea',
                            'is_required' => false,
                            'is_translatable' => true,
                            'sort_order' => 3,
                        ],
                        [
                            'name' => 'phone',
                            'label' => 'Phone',
                            'field_type' => 'text',
                            'is_required' => false,
                            'is_translatable' => false,
                            'sort_order' => 4,
                        ],
                        [
                            'name' => 'email',
                            'label' => 'Email',
                            'field_type' => 'text',
                            'is_required' => false,
                            'is_translatable' => false,
                            'sort_order' => 5,
                        ],
                        [
                            'name' => 'address',
                            'label' => 'Address',
                            'field_type' => 'textarea',
                            'is_required' => false,
                            'is_translatable' => true,
                            'sort_order' => 6,
                        ],
                    ],
                ],

                /*
                |--------------------------------------------------------------------------
                | CTA
                |--------------------------------------------------------------------------
                */
                [
                    'name' => 'Call To Action',
                    'slug' => 'cta',
                    'description' => 'Call-to-action section.',
                    'fields' => [
                        [
                            'name' => 'image',
                            'label' => 'Image',
                            'field_type' => 'responsive_image',
                            'is_required' => false,
                            'is_translatable' => false,
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'title',
                            'label' => 'Title',
                            'field_type' => 'text',
                            'is_required' => false,
                            'is_translatable' => true,
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'heading',
                            'label' => 'Heading',
                            'field_type' => 'text',
                            'is_required' => true,
                            'is_translatable' => true,
                            'sort_order' => 3,
                        ],
                        [
                            'name' => 'description',
                            'label' => 'Description',
                            'field_type' => 'textarea',
                            'is_required' => false,
                            'is_translatable' => true,
                            'sort_order' => 4,
                        ],
                        [
                            'name' => 'button_text',
                            'label' => 'Button Text',
                            'field_type' => 'text',
                            'is_required' => true,
                            'is_translatable' => true,
                            'sort_order' => 5,
                        ],
                        [
                            'name' => 'button_link',
                            'label' => 'Button Link',
                            'field_type' => 'url',
                            'is_required' => true,
                            'is_translatable' => false,
                            'sort_order' => 6,
                        ],
                    ],
                ],

                /*
                |--------------------------------------------------------------------------
                | Image & Text
                |--------------------------------------------------------------------------
                */
                [
                    'name' => 'Image & Text',
                    'slug' => 'image_text',
                    'description' => 'Image and text content section.',
                    'fields' => [
                        [
                            'name' => 'image',
                            'label' => 'Image',
                            'field_type' => 'responsive_image',
                            'is_required' => true,
                            'is_translatable' => false,
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'title',
                            'label' => 'Title',
                            'field_type' => 'text',
                            'is_required' => false,
                            'is_translatable' => true,
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'heading',
                            'label' => 'Heading',
                            'field_type' => 'text',
                            'is_required' => true,
                            'is_translatable' => true,
                            'sort_order' => 3,
                        ],
                        [
                            'name' => 'description',
                            'label' => 'Description',
                            'field_type' => 'richtext',
                            'is_required' => false,
                            'is_translatable' => true,
                            'sort_order' => 4,
                        ],
                        [
                            'name' => 'button_text',
                            'label' => 'Button Text',
                            'field_type' => 'text',
                            'is_required' => false,
                            'is_translatable' => true,
                            'sort_order' => 5,
                        ],
                        [
                            'name' => 'button_link',
                            'label' => 'Button Link',
                            'field_type' => 'url',
                            'is_required' => false,
                            'is_translatable' => false,
                            'sort_order' => 6,
                        ],
                    ],
                ],
            ];

            foreach ($components as $componentData) {

                $fields = $componentData['fields'];

                unset($componentData['fields']);

                $component = Component::updateOrCreate(
                    [
                        'slug' => $componentData['slug'],
                    ],
                    [
                        'name' => $componentData['name'],
                        'description' => $componentData['description'] ?? null,
                        'status' => 1,
                    ]
                );

                foreach ($fields as $fieldData) {

                    $children = $fieldData['children'] ?? [];

                    unset($fieldData['children']);

                    $field = ComponentField::updateOrCreate(
                        [
                            'component_id' => $component->id,
                            'parent_id' => null,
                            'name' => $fieldData['name'],
                        ],
                        [
                            'label' => $fieldData['label'],
                            'field_type' => $fieldData['field_type'],
                            'is_required' => $fieldData['is_required'] ?? false,
                            'is_translatable' => $fieldData['is_translatable'] ?? false,
                            'sort_order' => $fieldData['sort_order'] ?? 0,
                            'config' => $fieldData['config'] ?? null,
                        ]
                    );

                    foreach ($children as $childData) {

                        ComponentField::updateOrCreate(
                            [
                                'component_id' => $component->id,
                                'parent_id' => $field->id,
                                'name' => $childData['name'],
                            ],
                            [
                                'label' => $childData['label'],
                                'field_type' => $childData['field_type'],
                                'is_required' => $childData['is_required'] ?? false,
                                'is_translatable' => $childData['is_translatable'] ?? false,
                                'sort_order' => $childData['sort_order'] ?? 0,
                                'config' => $childData['config'] ?? null,
                            ]
                        );
                    }
                }
            }
        });
    }
}
