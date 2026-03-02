<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodTranslation;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languageIds = Language::query()
            ->whereIn('code', ['en', 'bn'])
            ->pluck('id', 'code');

        $methods = [
            [
                'code' => 'bkash',
                'sort_order' => 1,
                'is_active' => 1,
                'details_json' => [
                    'mobile_number' => '01XXXXXXXXX',
                ],
                'en' => [
                    'name' => 'bKash',
                    'description' => 'Mobile wallet payment via bKash.',
                ],
                'bn' => [
                    'name' => 'বিকাশ',
                    'description' => 'বিকাশ মোবাইল ওয়ালেটের মাধ্যমে পেমেন্ট।',
                ],
            ],
            [
                'code' => 'nagad',
                'sort_order' => 2,
                'is_active' => 1,
                'details_json' => [
                    'mobile_number' => '01XXXXXXXXX',
                ],
                'en' => [
                    'name' => 'Nagad',
                    'description' => 'Mobile wallet payment via Nagad.',
                ],
                'bn' => [
                    'name' => 'নগদ',
                    'description' => 'নগদ মোবাইল ওয়ালেটের মাধ্যমে পেমেন্ট।',
                ],
            ],
            [
                'code' => 'bank_payment',
                'sort_order' => 3,
                'is_active' => 1,
                'details_json' => [
                    'bank_name' => 'Default Bank',
                    'branch_name' => null,
                    'account_number' => '000000000000',
                ],
                'en' => [
                    'name' => 'Bank Payment',
                    'description' => 'Bank transfer payment. Bank name is required; branch is optional.',
                ],
                'bn' => [
                    'name' => 'ব্যাংক পেমেন্ট',
                    'description' => 'ব্যাংক ট্রান্সফার পেমেন্ট। ব্যাংকের নাম আবশ্যক, শাখার নাম ঐচ্ছিক।',
                ],
            ],
            [
                'code' => 'cash_payment',
                'sort_order' => 4,
                'is_active' => 1,
                'details_json' => null,
                'en' => [
                    'name' => 'Cash Payment',
                    'description' => 'Manual cash collection payment.',
                ],
                'bn' => [
                    'name' => 'ক্যাশ পেমেন্ট',
                    'description' => 'ম্যানুয়াল ক্যাশ কালেকশন পেমেন্ট।',
                ],
            ],
        ];

        foreach ($methods as $row) {
            $method = PaymentMethod::query()->updateOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['en']['name'],
                    'description' => $row['en']['description'],
                    'details_json' => $row['details_json'],
                    'sort_order' => $row['sort_order'],
                    'is_active' => $row['is_active'],
                ]
            );

            if (isset($languageIds['en'])) {
                PaymentMethodTranslation::query()->updateOrCreate(
                    [
                        'payment_method_id' => $method->id,
                        'language_id' => (int) $languageIds['en'],
                    ],
                    [
                        'name' => $row['en']['name'],
                        'description' => $row['en']['description'],
                    ]
                );
            }

            if (isset($languageIds['bn'])) {
                PaymentMethodTranslation::query()->updateOrCreate(
                    [
                        'payment_method_id' => $method->id,
                        'language_id' => (int) $languageIds['bn'],
                    ],
                    [
                        'name' => $row['bn']['name'],
                        'description' => $row['bn']['description'],
                    ]
                );
            }
        }
    }
}

