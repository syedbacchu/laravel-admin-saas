<?php

namespace Database\Seeders;

use App\Models\Component;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\SectionTranslation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {

            /*
            |--------------------------------------------------------------------------
            | Languages
            |--------------------------------------------------------------------------
            */

            $english = DB::table('languages')
                ->where('code', 'en')
                ->first();

            $bangla = DB::table('languages')
                ->where('code', 'bn')
                ->first();

            if (!$english || !$bangla) {
                throw new \Exception(
                    'English or Bangla language not found.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Create Test Page
            |--------------------------------------------------------------------------
            */

            $page = Page::updateOrCreate(
                [
                    'slug' => 'test-home',
                ],
                [
                    'name' => 'Test Home',
                    'meta_title' => 'Test Home Page',
                    'meta_description' => 'Dynamic page builder test page.',
                    'meta_image' => null,
                    'status' => true,
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | Components
            |--------------------------------------------------------------------------
            */

            $components = [
                'slider',
                'text_section',
                'features',
                'services',
                'gallery',
                'testimonials',
                'contact',
                'cta',
                'image_text',
            ];


            foreach ($components as $index => $componentSlug) {

                $component = Component::where(
                    'slug',
                    $componentSlug
                )->first();

                if (!$component) {
                    throw new \Exception(
                        "Component not found: {$componentSlug}"
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Page Section
                |--------------------------------------------------------------------------
                */

                $section = PageSection::updateOrCreate(
                    [
                        'page_id' => $page->id,
                        'component_id' => $component->id,
                    ],
                    [
                        'sort_order' => $index + 1,
                        'is_visible' => 1,
                    ]
                );


                /*
                |--------------------------------------------------------------------------
                | English Data
                |--------------------------------------------------------------------------
                */

                SectionTranslation::updateOrCreate(
                    [
                        'page_section_id' => $section->id,
                        'language_id' => $english->id,
                    ],
                    [
                        'data' => $this->getEnglishData(
                            $componentSlug
                        ),
                    ]
                );


                /*
                |--------------------------------------------------------------------------
                | Bangla Data
                |--------------------------------------------------------------------------
                */

                SectionTranslation::updateOrCreate(
                    [
                        'page_section_id' => $section->id,
                        'language_id' => $bangla->id,
                    ],
                    [
                        'data' => $this->getBanglaData(
                            $componentSlug
                        ),
                    ]
                );
            }
        });
    }


    /*
    |--------------------------------------------------------------------------
    | English Data
    |--------------------------------------------------------------------------
    */

    private function getEnglishData(string $component): array
    {
        return match ($component) {

            'slider' => [
                'slides' => [
                    [
                        'image' => [
                            'desktop' => '',
                            'mobile' => '',
                        ],
                        'title' => 'Welcome to Bio-Xin',
                        'heading' => 'Your Beauty Journey Starts Here',
                        'description' => 'Discover our premium beauty and skincare services.',
                        'button_text' => 'Book Now',
                        'button_link' => '/booking',
                    ],
                    [
                        'image' => [
                            'desktop' => '',
                            'mobile' => '',
                        ],
                        'title' => 'Beautiful You',
                        'heading' => 'Feel Confident in Your Skin',
                        'description' => 'Experience professional skincare treatments.',
                        'button_text' => 'Explore Services',
                        'button_link' => '/services',
                    ],
                ],
            ],

            'text_section' => [
                'title' => 'About Us',
                'heading' => 'We Care About Your Beauty',
                'description' => 'Bio-Xin provides professional beauty and skincare solutions designed around your needs.',
                'button_text' => 'Learn More',
                'button_link' => '/about-us',
            ],

            'features' => [
                'title' => 'Why Choose Us',
                'description' => 'Everything you need for a better beauty experience.',
                'items' => [
                    [
                        'icon' => '',
                        'title' => 'Professional Experts',
                        'description' => 'Our services are provided by trained professionals.',
                        'link' => '/about-us',
                    ],
                    [
                        'icon' => '',
                        'title' => 'Modern Technology',
                        'description' => 'We use modern technology and advanced techniques.',
                        'link' => '/services',
                    ],
                    [
                        'icon' => '',
                        'title' => 'Trusted Service',
                        'description' => 'Thousands of customers trust our services.',
                        'link' => '/about-us',
                    ],
                ],
            ],

            'services' => [
                'title' => 'Our Services',
                'heading' => 'Explore Our Beauty Services',
                'description' => 'Choose from our wide range of professional treatments.',
                'items' => [
                    [
                        'image' => [
                            'desktop' => '',
                            'mobile' => '',
                        ],
                        'title' => 'Facial Treatment',
                        'description' => 'Professional facial treatment for healthy and glowing skin.',
                        'link' => '/services/facial-treatment',
                    ],
                    [
                        'image' => [
                            'desktop' => '',
                            'mobile' => '',
                        ],
                        'title' => 'Laser Treatment',
                        'description' => 'Advanced laser treatment performed by professionals.',
                        'link' => '/services/laser-treatment',
                    ],
                    [
                        'image' => [
                            'desktop' => '',
                            'mobile' => '',
                        ],
                        'title' => 'Skin Care',
                        'description' => 'Personalized skincare solutions for your skin.',
                        'link' => '/services/skin-care',
                    ],
                ],
            ],

            'gallery' => [
                'title' => 'Our Gallery',
                'description' => 'Take a look at some of our work.',
                'items' => [
                    [
                        'image' => [
                            'desktop' => '',
                            'mobile' => '',
                        ],
                        'title' => 'Treatment Room',
                        'link' => '/gallery',
                    ],
                    [
                        'image' => [
                            'desktop' => '',
                            'mobile' => '',
                        ],
                        'title' => 'Beauty Treatment',
                        'link' => '/gallery',
                    ],
                    [
                        'image' => [
                            'desktop' => '',
                            'mobile' => '',
                        ],
                        'title' => 'Our Branch',
                        'link' => '/gallery',
                    ],
                ],
            ],

            'testimonials' => [
                'title' => 'What Our Customers Say',
                'description' => 'Hear from our happy customers.',
                'items' => [
                    [
                        'image' => '',
                        'name' => 'Sarah Ahmed',
                        'designation' => 'Customer',
                        'message' => 'I had a wonderful experience with the service.',
                        'rating' => 5,
                    ],
                    [
                        'image' => '',
                        'name' => 'Nadia Rahman',
                        'designation' => 'Customer',
                        'message' => 'The staff were very professional and helpful.',
                        'rating' => 5,
                    ],
                ],
            ],

            'contact' => [
                'title' => 'Get In Touch',
                'heading' => 'We Would Love To Hear From You',
                'description' => 'Contact us for any questions or service information.',
                'phone' => '+880 1234 567890',
                'email' => 'info@example.com',
                'address' => 'Dhaka, Bangladesh',
            ],

            'cta' => [
                'image' => [
                    'desktop' => '',
                    'mobile' => '',
                ],
                'title' => 'Ready To Get Started?',
                'heading' => 'Book Your Appointment Today',
                'description' => 'Take the first step towards better skin and confidence.',
                'button_text' => 'Book Appointment',
                'button_link' => '/booking',
            ],

            'image_text' => [
                'image' => [
                    'desktop' => '',
                    'mobile' => '',
                ],
                'title' => 'About Bio-Xin',
                'heading' => 'Your Trusted Beauty Partner',
                'description' => 'We are dedicated to providing quality beauty and skincare services to our customers.',
                'button_text' => 'About Us',
                'button_link' => '/about-us',
            ],

            default => [],
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Bangla Data
    |--------------------------------------------------------------------------
    */

    private function getBanglaData(string $component): array
    {
        return match ($component) {

            'slider' => [
                'slides' => [
                    [
                        'image' => [
                            'desktop' => '',
                            'mobile' => '',
                        ],
                        'title' => 'Bio-Xin-এ স্বাগতম',
                        'heading' => 'আপনার সৌন্দর্যের যাত্রা শুরু হোক',
                        'description' => 'আমাদের প্রিমিয়াম বিউটি ও স্কিনকেয়ার সেবাগুলো আবিষ্কার করুন।',
                        'button_text' => 'এখনই বুক করুন',
                        'button_link' => '/booking',
                    ],
                    [
                        'image' => [
                            'desktop' => '',
                            'mobile' => '',
                        ],
                        'title' => 'নিজেকে সুন্দর রাখুন',
                        'heading' => 'নিজের ত্বকে আত্মবিশ্বাসী হোন',
                        'description' => 'প্রফেশনাল স্কিনকেয়ার ট্রিটমেন্টের অভিজ্ঞতা নিন।',
                        'button_text' => 'সেবাগুলো দেখুন',
                        'button_link' => '/services',
                    ],
                ],
            ],

            'text_section' => [
                'title' => 'আমাদের সম্পর্কে',
                'heading' => 'আমরা আপনার সৌন্দর্যের যত্ন নিই',
                'description' => 'Bio-Xin আপনার প্রয়োজন অনুযায়ী প্রফেশনাল বিউটি ও স্কিনকেয়ার সেবা প্রদান করে।',
                'button_text' => 'আরও জানুন',
                'button_link' => '/about-us',
            ],

            'features' => [
                'title' => 'কেন আমাদের বেছে নেবেন',
                'description' => 'আপনার জন্য একটি উন্নত বিউটি এক্সপেরিয়েন্স।',
                'items' => [
                    [
                        'icon' => '',
                        'title' => 'অভিজ্ঞ বিশেষজ্ঞ',
                        'description' => 'আমাদের সেবাগুলো প্রশিক্ষিত প্রফেশনালদের মাধ্যমে প্রদান করা হয়।',
                        'link' => '/about-us',
                    ],
                    [
                        'icon' => '',
                        'title' => 'আধুনিক প্রযুক্তি',
                        'description' => 'আমরা আধুনিক প্রযুক্তি ও উন্নত পদ্ধতি ব্যবহার করি।',
                        'link' => '/services',
                    ],
                    [
                        'icon' => '',
                        'title' => 'বিশ্বস্ত সেবা',
                        'description' => 'হাজারো গ্রাহক আমাদের সেবার ওপর আস্থা রাখেন।',
                        'link' => '/about-us',
                    ],
                ],
            ],

            'services' => [
                'title' => 'আমাদের সেবাসমূহ',
                'heading' => 'আমাদের বিউটি সেবাগুলো দেখুন',
                'description' => 'আমাদের বিভিন্ন প্রফেশনাল ট্রিটমেন্ট থেকে আপনার পছন্দের সেবা বেছে নিন।',
                'items' => [
                    [
                        'image' => [
                            'desktop' => '',
                            'mobile' => '',
                        ],
                        'title' => 'ফেসিয়াল ট্রিটমেন্ট',
                        'description' => 'স্বাস্থ্যকর ও উজ্জ্বল ত্বকের জন্য প্রফেশনাল ফেসিয়াল ট্রিটমেন্ট।',
                        'link' => '/services/facial-treatment',
                    ],
                    [
                        'image' => [
                            'desktop' => '',
                            'mobile' => '',
                        ],
                        'title' => 'লেজার ট্রিটমেন্ট',
                        'description' => 'অভিজ্ঞ বিশেষজ্ঞদের মাধ্যমে আধুনিক লেজার ট্রিটমেন্ট।',
                        'link' => '/services/laser-treatment',
                    ],
                    [
                        'image' => [
                            'desktop' => '',
                            'mobile' => '',
                        ],
                        'title' => 'স্কিন কেয়ার',
                        'description' => 'আপনার ত্বকের জন্য প্রয়োজন অনুযায়ী স্কিনকেয়ার সলিউশন।',
                        'link' => '/services/skin-care',
                    ],
                ],
            ],

            'gallery' => [
                'title' => 'আমাদের গ্যালারি',
                'description' => 'আমাদের কাজের কিছু ছবি দেখুন।',
                'items' => [
                    [
                        'image' => [
                            'desktop' => '',
                            'mobile' => '',
                        ],
                        'title' => 'ট্রিটমেন্ট রুম',
                        'link' => '/gallery',
                    ],
                    [
                        'image' => [
                            'desktop' => '',
                            'mobile' => '',
                        ],
                        'title' => 'বিউটি ট্রিটমেন্ট',
                        'link' => '/gallery',
                    ],
                    [
                        'image' => [
                            'desktop' => '',
                            'mobile' => '',
                        ],
                        'title' => 'আমাদের ব্রাঞ্চ',
                        'link' => '/gallery',
                    ],
                ],
            ],

            'testimonials' => [
                'title' => 'আমাদের গ্রাহকরা কী বলেন',
                'description' => 'আমাদের সন্তুষ্ট গ্রাহকদের অভিজ্ঞতা।',
                'items' => [
                    [
                        'image' => '',
                        'name' => 'সারা আহমেদ',
                        'designation' => 'গ্রাহক',
                        'message' => 'সেবাটি নিয়ে আমার অভিজ্ঞতা অসাধারণ ছিল।',
                        'rating' => 5,
                    ],
                    [
                        'image' => '',
                        'name' => 'নাদিয়া রহমান',
                        'designation' => 'গ্রাহক',
                        'message' => 'স্টাফরা অনেক প্রফেশনাল ও সহযোগিতাপূর্ণ ছিলেন।',
                        'rating' => 5,
                    ],
                ],
            ],

            'contact' => [
                'title' => 'যোগাযোগ করুন',
                'heading' => 'আমরা আপনার কথা শুনতে চাই',
                'description' => 'যেকোনো প্রশ্ন বা সেবার তথ্যের জন্য আমাদের সাথে যোগাযোগ করুন।',
                'phone' => '+880 1234 567890',
                'email' => 'info@example.com',
                'address' => 'ঢাকা, বাংলাদেশ',
            ],

            'cta' => [
                'image' => [
                    'desktop' => '',
                    'mobile' => '',
                ],
                'title' => 'শুরু করার জন্য প্রস্তুত?',
                'heading' => 'আজই আপনার অ্যাপয়েন্টমেন্ট বুক করুন',
                'description' => 'আরও সুন্দর ও আত্মবিশ্বাসী ত্বকের জন্য আজই প্রথম পদক্ষেপ নিন।',
                'button_text' => 'অ্যাপয়েন্টমেন্ট বুক করুন',
                'button_link' => '/booking',
            ],

            'image_text' => [
                'image' => [
                    'desktop' => '',
                    'mobile' => '',
                ],
                'title' => 'Bio-Xin সম্পর্কে',
                'heading' => 'আপনার বিশ্বস্ত বিউটি পার্টনার',
                'description' => 'আমরা আমাদের গ্রাহকদের মানসম্মত বিউটি ও স্কিনকেয়ার সেবা দিতে প্রতিশ্রুতিবদ্ধ।',
                'button_text' => 'আমাদের সম্পর্কে',
                'button_link' => '/about-us',
            ],

            default => [],
        };
    }
}
