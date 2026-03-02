<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\FeatureTranslation;
use App\Models\Language;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languageIds = Language::query()
            ->whereIn('code', ['en', 'bn'])
            ->pluck('id', 'code');

        $features = [
            [
                'key' => 'vehicle.manage_1_5',
                'value_type' => 'boolean',
                'en' => '1 to 5 Vehicle Management',
                'bn' => '১ থেকে ৫টি গাড়ি ম্যানেজমেন্ট',
            ],
            [
                'key' => 'vehicle.manage_5_10',
                'value_type' => 'boolean',
                'en' => '5 to 10 Vehicle Management',
                'bn' => '৫ থেকে ১০টি গাড়ি ম্যানেজমেন্ট',
            ],
            [
                'key' => 'vehicle.manage_10_20',
                'value_type' => 'boolean',
                'en' => '10 to 20 Vehicle Management',
                'bn' => '১০-২০টি গাড়ি ম্যানেজমেন্ট',
            ],
            [
                'key' => 'vehicle.manage_20_50',
                'value_type' => 'boolean',
                'en' => '20 to 50 Vehicle Management',
                'bn' => '২০-৫০টি গাড়ি ম্যানেজমেন্ট',
            ],
            [
                'key' => 'trip.monitoring',
                'value_type' => 'boolean',
                'en' => 'Trip Accounting and Monitoring',
                'bn' => 'ট্রিপ হিসাব ও মনিটরিং',
            ],
            [
                'key' => 'road.expense_tracking',
                'value_type' => 'boolean',
                'en' => 'Road Expense Tracking',
                'bn' => 'রোড এক্সপেন্স ট্র্যাকিং',
            ],
            [
                'key' => 'fuel.management',
                'value_type' => 'boolean',
                'en' => 'Fuel Management',
                'bn' => 'জ্বালানি ব্যবস্থাপনা',
            ],
            [
                'key' => 'service.maintenance',
                'value_type' => 'boolean',
                'en' => 'Servicing and Maintenance',
                'bn' => 'সার্ভিসিং ও রক্ষণাবেক্ষণ',
            ],
            [
                'key' => 'vehicle.installment_payment',
                'value_type' => 'boolean',
                'en' => 'Vehicle Installment and Payment Tracking',
                'bn' => 'গাড়ির কিস্তি ও পেমেন্ট হিসাব',
            ],
            [
                'key' => 'parts.stock_management',
                'value_type' => 'boolean',
                'en' => 'Parts and Stock Management',
                'bn' => 'পার্টস ও স্টক ম্যানেজমেন্ট',
            ],
            [
                'key' => 'billing.digital_invoice',
                'value_type' => 'boolean',
                'en' => 'Digital Bill and Invoice Generation',
                'bn' => 'ডিজিটাল বিল ও চালান তৈরি',
            ],
            [
                'key' => 'voucher.receipt_generation',
                'value_type' => 'boolean',
                'en' => 'Voucher and Receipt Generation',
                'bn' => 'ভাউচার ও রসিদ তৈরি',
            ],
            [
                'key' => 'receivable.tracking',
                'value_type' => 'boolean',
                'en' => 'Receivable Tracking',
                'bn' => 'পাওনার হিসাব ও ট্র্যাকিং',
            ],
            [
                'key' => 'notification.sms_email',
                'value_type' => 'boolean',
                'en' => 'SMS/Email Notification System',
                'bn' => 'SMS/Email নোটিফিকেশন সিস্টেম',
            ],
            [
                'key' => 'gps.api_integration',
                'value_type' => 'boolean',
                'en' => 'GPS Tracking and API Integration',
                'bn' => 'GPS ট্র্যাকিং ও API ইন্ট্রিগেশন',
            ],
            [
                'key' => 'user.multi_access_10',
                'value_type' => 'boolean',
                'en' => 'Multi-user Access (Up to 10 Users)',
                'bn' => 'মাল্টি-ইউজার এক্সেস (১০ জন পর্যন্ত)',
            ],
            [
                'key' => 'hr.attendance_leave',
                'value_type' => 'boolean',
                'en' => 'Attendance and Leave Management',
                'bn' => 'হাজিরা ও ছুটি ম্যানেজমেন্ট',
            ],
            [
                'key' => 'payroll.salary_commission',
                'value_type' => 'boolean',
                'en' => 'Salary and Commission Management',
                'bn' => 'বেতন ও কমিশন হিসাব',
            ],
            [
                'key' => 'finance.balance_cashflow_report',
                'value_type' => 'boolean',
                'en' => 'Balance Statement and Cashflow Report',
                'bn' => 'ব্যালেন্স স্টেটমেন্ট ও ক্যাশফ্লো রিপোর্ট',
            ],
            [
                'key' => 'support.dedicated_account_manager',
                'value_type' => 'boolean',
                'en' => 'Dedicated Account Manager and Live Support',
                'bn' => 'ডেডিকেটেড একাউন্ট ম্যানেজার ও লাইভ সাপোর্ট',
            ],
            [
                'key' => 'workflow.approval_verification',
                'value_type' => 'boolean',
                'en' => 'Approval and Verification System',
                'bn' => 'অ্যাপ্রুভাল ও ভেরিফাই সিস্টেম',
            ],
            [
                'key' => 'reservation.booking_management',
                'value_type' => 'boolean',
                'en' => 'Reservation and Booking Management',
                'bn' => 'রিজার্ভেশন ও বুকিং ব্যবস্থা',
            ],
            [
                'key' => 'fuel.intelligence',
                'value_type' => 'boolean',
                'en' => 'Fuel Intelligence',
                'bn' => 'ফুয়েল ইনটেলিজেন্স',
            ],
        ];

        foreach ($features as $row) {
            $feature = Feature::query()->updateOrCreate(
                ['key' => $row['key']],
                [
                    'name' => $row['en'],
                    'description' => null,
                    'value_type' => $row['value_type'],
                    'is_active' => 1,
                ]
            );

            if (isset($languageIds['en'])) {
                FeatureTranslation::query()->updateOrCreate(
                    [
                        'feature_id' => $feature->id,
                        'language_id' => (int) $languageIds['en'],
                    ],
                    [
                        'name' => $row['en'],
                        'description' => null,
                    ]
                );
            }

            if (isset($languageIds['bn'])) {
                FeatureTranslation::query()->updateOrCreate(
                    [
                        'feature_id' => $feature->id,
                        'language_id' => (int) $languageIds['bn'],
                    ],
                    [
                        'name' => $row['bn'],
                        'description' => null,
                    ]
                );
            }
        }
    }
}

