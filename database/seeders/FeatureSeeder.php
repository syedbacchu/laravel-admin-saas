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
            // ============================================
            // VEHICLE MANAGEMENT FEATURES
            // ============================================
            [
                'key' => 'vehicle.management',
                'value_type' => 'boolean',
                'category' => 'vehicle_management',
                'is_group' => 0,
                'sort_order' => 10,
                'en' => 'Vehicle Management',
                'bn' => 'গাড়ি ম্যানেজমেন্ট',
            ],[
                'key' => 'vehicle.manage_1_5',
                'value_type' => 'boolean',
                'category' => 'vehicle_management',
                'is_group' => 1,
                'sort_order' => 10,
                'en' => '1 to 5 Vehicle Management',
                'bn' => '১ থেকে ৫টি গাড়ি ম্যানেজমেন্ট',
            ],
            [
                'key' => 'vehicle.manage_5_10',
                'value_type' => 'boolean',
                'category' => 'vehicle_management',
                'is_group' => 1,
                'sort_order' => 11,
                'en' => '5 to 10 Vehicle Management',
                'bn' => '৫ থেকে ১০টি গাড়ি ম্যানেজমেন্ট',
            ],
            [
                'key' => 'vehicle.manage_10_20',
                'value_type' => 'boolean',
                'category' => 'vehicle_management',
                'is_group' => 1,
                'sort_order' => 12,
                'en' => '10 to 20 Vehicle Management',
                'bn' => '১০-২০টি গাড়ি ম্যানেজমেন্ট',
            ],
            [
                'key' => 'vehicle.manage_20_50',
                'value_type' => 'boolean',
                'category' => 'vehicle_management',
                'is_group' => 1,
                'sort_order' => 13,
                'en' => '20 to 50 Vehicle Management',
                'bn' => '২০-৫০টি গাড়ি ম্যানেজমেন্ট',
            ],
            [
                'key' => 'vehicle.manage_unlimited',
                'value_type' => 'boolean',
                'category' => 'vehicle_management',
                'is_group' => 1,
                'sort_order' => 14,
                'en' => 'Unlimited Vehicle Management',
                'bn' => 'অনির্দিষ্ট সংখ্যক গাড়ি ম্যানেজমেন্ট',
            ],
            [
                'key' => 'vehicle.installment_payment',
                'value_type' => 'boolean',
                'category' => 'vehicle_management',
                'is_group' => 1,
                'sort_order' => 15,
                'en' => 'Vehicle Installment and Payment Tracking',
                'bn' => 'গাড়ির কিস্তি ও পেমেন্ট হিসাব',
            ],

            // ============================================
            // TRIP MANAGEMENT FEATURES
            // ============================================
            [
                'key' => 'trip.monitoring',
                'value_type' => 'boolean',
                'category' => 'trip_management',
                'is_group' => 1,
                'sort_order' => 20,
                'en' => 'Trip Accounting and Monitoring',
                'bn' => 'ট্রিপ হিসাব ও মনিটরিং',
            ],
            [
                'key' => 'trip.own_transport',
                'value_type' => 'boolean',
                'category' => 'trip_management',
                'is_group' => 0,
                'sort_order' => 21,
                'en' => 'Own Transport Trip Management',
                'bn' => 'নিজস্ব পরিবহন ট্রিপ ব্যবস্থাপনা',
            ],
            [
                'key' => 'trip.vendor_transport',
                'value_type' => 'boolean',
                'category' => 'trip_management',
                'is_group' => 0,
                'sort_order' => 22,
                'en' => 'Vendor Transport Trip Management',
                'bn' => 'ভেন্ডর পরিবহন ট্রিপ ব্যবস্থাপনা',
            ],
            [
                'key' => 'road.expense_tracking',
                'value_type' => 'boolean',
                'category' => 'trip_management',
                'is_group' => 1,
                'sort_order' => 23,
                'en' => 'Road Expense Tracking',
                'bn' => 'রোড এক্সপেন্স ট্র্যাকিং',
            ],
            [
                'key' => 'trip.billing',
                'value_type' => 'boolean',
                'category' => 'trip_management',
                'is_group' => 0,
                'sort_order' => 23,
                'en' => 'Trip Billing',
                'bn' => 'ট্রিপ বিল ব্যবস্থাপনা',
            ],
            [
                'key' => 'billing.digital_invoice',
                'value_type' => 'boolean',
                'category' => 'trip_management',
                'is_group' => 1,
                'sort_order' => 81,
                'en' => 'Digital Bill and Invoice Generation',
                'bn' => 'ডিজিটাল বিল ও চালান তৈরি',
            ],
            [
                'key' => 'voucher.receipt_generation',
                'value_type' => 'boolean',
                'category' => 'trip_management',
                'is_group' => 1,
                'sort_order' => 82,
                'en' => 'Voucher and Receipt Generation',
                'bn' => 'ভাউচার ও রসিদ তৈরি',
            ],
            [
                'key' => 'gps.api_integration',
                'value_type' => 'boolean',
                'category' => 'trip_management',
                'is_group' => 1,
                'sort_order' => 111,
                'en' => 'GPS Tracking and API Integration',
                'bn' => 'GPS ট্র্যাকিং ও API ইন্ট্রিগেশন',
            ],

            // ============================================
            // VENDOR MANAGEMENT
            // ============================================

            [
                'key' => 'vendor.management',
                'value_type' => 'boolean',
                'category' => 'vendor_management',
                'is_group' => 1,
                'sort_order' => 32,
                'en' => 'Vendor Management',
                'bn' => 'ভেন্ডর ব্যবস্থাপনা',
            ],
            [
                'key' => 'vendor.rent_vehicles',
                'value_type' => 'boolean',
                'category' => 'vendor_management',
                'is_group' => 0,
                'sort_order' => 33,
                'en' => 'Rent Vehicle Management',
                'bn' => 'ভাড়া যানবাহন ব্যবস্থাপনা',
            ],

            // ============================================
            // Basic Setup and  MANAGEMENT
            // ============================================
            [
                'key' => 'customer.management',
                'value_type' => 'boolean',
                'category' => 'basic_setup',
                'is_group' => 0,
                'sort_order' => 30,
                'en' => 'Customer Management',
                'bn' => 'গ্রাহক ব্যবস্থাপনা',
            ],
            [
                'key' => 'supplier.management',
                'value_type' => 'boolean',
                'category' => 'basic_setup',
                'is_group' => 0,
                'sort_order' => 40,
                'en' => 'Supplier Management',
                'bn' => 'সরবরাহকারী ব্যবস্থাপনা',
            ],

            [
                'key' => 'driver.management',
                'value_type' => 'boolean',
                'category' => 'basic_setup',
                'is_group' => 0,
                'sort_order' => 51,
                'en' => 'Driver Management',
                'bn' => 'ড্রাইভার ব্যবস্থাপনা',
            ],
            [
                'key' => 'helper.management',
                'value_type' => 'boolean',
                'category' => 'basic_setup',
                'is_group' => 0,
                'sort_order' => 53,
                'en' => 'Helper Management',
                'bn' => 'হেল্পার ব্যবস্থাপনা',
            ],
            [
                'key' => 'supervisor.management',
                'value_type' => 'boolean',
                'category' => 'basic_setup',
                'is_group' => 0,
                'sort_order' => 54,
                'en' => 'Supervisor Management',
                'bn' => 'সুপারভাইজার ব্যবস্থাপনা',
            ],
            [
                'key' => 'office.management',
                'value_type' => 'boolean',
                'category' => 'basic_setup',
                'is_group' => 0,
                'sort_order' => 100,
                'en' => 'Office and Branch Management',
                'bn' => 'অফিস ও শাখা ব্যবস্থাপনা',
            ],
            [
                'key' => 'area.zone_management',
                'value_type' => 'boolean',
                'category' => 'basic_setup',
                'is_group' => 0,
                'sort_order' => 101,
                'en' => 'Area and Zone Management',
                'bn' => 'এলাকা ও জোন ব্যবস্থাপনা',
            ],
            [
                'key' => 'route.pricing',
                'value_type' => 'boolean',
                'category' => 'basic_setup',
                'is_group' => 1,
                'sort_order' => 102,
                'en' => 'Route Pricing Management',
                'bn' => 'রুট মূল্য ব্যবস্থাপনা',
            ],
            // ============================================
            // SUPPLIER  Service & INVENTORY MANAGEMENT
            // ============================================

            [
                'key' => 'service.maintenance_services',
                'value_type' => 'boolean',
                'category' => 'service_and_maintenance',
                'is_group' => 1,
                'sort_order' => 41,
                'en' => 'Servicing and Maintenance',
                'bn' => 'সার্ভিসিং ও রক্ষণাবেক্ষণ',
            ],
            [
                'key' => 'parts.stock_management',
                'value_type' => 'boolean',
                'category' => 'service_and_maintenance',
                'is_group' => 0,
                'sort_order' => 42,
                'en' => 'Parts and Stock Management',
                'bn' => 'পার্টস ও স্টক ম্যানেজমেন্ট',
            ],
            [
                'key' => 'finance.daily_office_expenses',
                'value_type' => 'boolean',
                'category' => 'service_and_maintenance',
                'is_group' => 0,
                'sort_order' => 85,
                'en' => 'Office Expenses Management',
                'bn' => 'অফিস খরচ ব্যবস্থাপনা',
            ],


            // ============================================
            // Multi STAFF MANAGEMENT
            // ============================================

            [
                'key' => 'staff.multi_user_access',
                'value_type' => 'boolean',
                'category' => 'multi_user_access',
                'is_group' => 0,
                'sort_order' => 55,
                'en' => 'Multi-user/Staff Access',
                'bn' => 'মাল্টি-ইউজার স্টাফ এক্সেস',
            ],
            [
                'key' => 'staff.multi_user_access_2',
                'value_type' => 'boolean',
                'category' => 'multi_user_access',
                'is_group' => 1,
                'sort_order' => 55,
                'en' => 'Multi-user Access (up to 2 users)',
                'bn' => 'মাল্টি-ইউজার এক্সেস (2 ইউজার)',
            ],
            [
                'key' => 'staff.multi_user_access_3',
                'value_type' => 'boolean',
                'category' => 'multi_user_access',
                'is_group' => 1,
                'sort_order' => 55,
                'en' => 'Multi-user Access (up to 3 users)',
                'bn' => 'মাল্টি-ইউজার এক্সেস (3 ইউজার)',
            ],
            [
                'key' => 'staff.multi_user_access_5',
                'value_type' => 'boolean',
                'category' => 'multi_user_access',
                'is_group' => 1,
                'sort_order' => 55,
                'en' => 'Multi-user Access (up to 5 users)',
                'bn' => 'মাল্টি-ইউজার এক্সেস (5 ইউজার)',
            ],
            [
                'key' => 'staff.multi_user_access_10',
                'value_type' => 'boolean',
                'category' => 'multi_user_access',
                'is_group' => 1,
                'sort_order' => 55,
                'en' => 'Multi-user Access (up to 10 users)',
                'bn' => 'মাল্টি-ইউজার এক্সেস (10 ইউজার)',
            ],

            // ============================================
            // HR & PAYROLL FEATURES
            // ============================================
            [
                'key' => 'hr.payroll',
                'value_type' => 'boolean',
                'category' => 'hr_and_payroll_management',
                'is_group' => 1,
                'sort_order' => 60,
                'en' => 'HR & Payroll Management',
                'bn' => 'কর্মী ও পে-রোল ব্যবস্থাপনা',
            ],
            [
                'key' => 'employee.management',
                'value_type' => 'boolean',
                'category' => 'hr_and_payroll_management',
                'is_group' => 0,
                'sort_order' => 50,
                'en' => 'Employee Management',
                'bn' => 'কর্মচারী ব্যবস্থাপনা',
            ],
            [
                'key' => 'hr.attendance_leave',
                'value_type' => 'boolean',
                'category' => 'hr_and_payroll_management',
                'is_group' => 0,
                'sort_order' => 60,
                'en' => 'Attendance and Leave Management',
                'bn' => 'হাজিরা ও ছুটি ম্যানেজমেন্ট',
            ],
            [
                'key' => 'payroll.salary_commission',
                'value_type' => 'boolean',
                'category' => 'hr_and_payroll_management',
                'is_group' => 1,
                'sort_order' => 61,
                'en' => 'Salary and Commission Management',
                'bn' => 'বেতন ও কমিশন হিসাব',
            ],
            [
                'key' => 'payroll.advance_salary',
                'value_type' => 'boolean',
                'category' => 'hr_and_payroll_management',
                'is_group' => 0,
                'sort_order' => 62,
                'en' => 'Advance Salary Management',
                'bn' => 'অগ্রিম বেতন ব্যবস্থাপনা',
            ],
            [
                'key' => 'payroll.loan_management',
                'value_type' => 'boolean',
                'category' => 'hr_and_payroll_management',
                'is_group' => 1,
                'sort_order' => 63,
                'en' => 'Employee Loan Management',
                'bn' => 'কর্মচারী ঋণ ব্যবস্থাপনা',
            ],
            [
                'key' => 'payroll.bonus_management',
                'value_type' => 'boolean',
                'category' => 'hr_and_payroll_management',
                'is_group' => 0,
                'sort_order' => 64,
                'en' => 'Bonus and Incentive Management',
                'bn' => 'বোনাস ও প্রণোদনা ব্যবস্থাপনা',
            ],
            [
                'key' => 'payroll.salary_sheet_generation',
                'value_type' => 'boolean',
                'category' => 'hr_and_payroll_management',
                'is_group' => 0,
                'sort_order' => 65,
                'en' => 'Automated Salary Sheet Generation',
                'bn' => 'স্বয়ংক্রিয় বেতন শিট তৈরি',
            ],

            // ============================================
            // FUEL & MAINTENANCE FEATURES
            // ============================================
            [
                'key' => 'fuel.management',
                'value_type' => 'boolean',
                'category' => 'fuel_management',
                'is_group' => 1,
                'sort_order' => 70,
                'en' => 'Fuel Management',
                'bn' => 'জ্বালানি ব্যবস্থাপনা',
            ],
            [
                'key' => 'fuel.intelligence',
                'value_type' => 'boolean',
                'category' => 'fuel_management',
                'is_group' => 1,
                'sort_order' => 71,
                'en' => 'Fuel Intelligence and Analytics',
                'bn' => 'ফুয়েল ইনটেলিজেন্স ও বিশ্লেষণ',
            ],
            [
                'key' => 'fuel.ledger_tracking',
                'value_type' => 'boolean',
                'category' => 'fuel_management',
                'is_group' => 1,
                'sort_order' => 72,
                'en' => 'Fuel Ledger and Consumption Tracking',
                'bn' => 'জ্বালানি লেজার ও খরচ ট্র্যাকিং',
            ],

            // ============================================
            // FINANCIAL MANAGEMENT
            // ============================================
            [
                'key' => 'receivable.tracking',
                'value_type' => 'boolean',
                'category' => 'finance_management',
                'is_group' => 0,
                'sort_order' => 80,
                'en' => 'Receivable Tracking',
                'bn' => 'পাওনার হিসাব ও ট্র্যাকিং',
            ],
            [
                'key' => 'fund.transfer',
                'value_type' => 'boolean',
                'category' => 'finance_management',
                'is_group' => 0,
                'sort_order' => 83,
                'en' => 'Fund Transfer Management',
                'bn' => 'তহবিল স্থানান্তর ব্যবস্থাপনা',
            ],
            [
                'key' => 'supplier.payment',
                'value_type' => 'boolean',
                'category' => 'finance_management',
                'is_group' => 0,
                'sort_order' => 80,
                'en' => 'Supplier Payment',
                'bn' => 'সাপ্লায়ার পেমেন্ট ব্যবস্থাপনা',
            ],
            [
                'key' => 'vendor.payment',
                'value_type' => 'boolean',
                'category' => 'finance_management',
                'is_group' => 0,
                'sort_order' => 80,
                'en' => 'Vendor Payment',
                'bn' => 'ভেন্ডর পেমেন্ট ব্যবস্থাপনা',
            ],
            [
                'key' => 'driver.payment_tracking',
                'value_type' => 'boolean',
                'category' => 'finance_management',
                'is_group' => 0,
                'sort_order' => 52,
                'en' => 'Driver Payment Tracking',
                'bn' => 'ড্রাইভার পেমেন্ট ট্র্যাকিং',
            ],


            // ============================================
            // Account MANAGEMENT
            // ============================================

            [
                'key' => 'customer.ledger',
                'value_type' => 'boolean',
                'category' => 'accounting',
                'is_group' => 0,
                'sort_order' => 31,
                'en' => 'Customer Ledger',
                'bn' => 'কাস্টমার লেজার',
            ],
            [
                'key' => 'supplier.ledger',
                'value_type' => 'boolean',
                'category' => 'accounting',
                'is_group' => 0,
                'sort_order' => 31,
                'en' => 'Supplier Ledger',
                'bn' => 'সাপ্লায়ার লেজার',
            ],
            [
                'key' => 'vendor.ledger',
                'value_type' => 'boolean',
                'category' => 'accounting',
                'is_group' => 0,
                'sort_order' => 31,
                'en' => 'Vendor Ledger',
                'bn' => 'ভেন্ডর লেজার',
            ],
            [
                'key' => 'driver.ledger',
                'value_type' => 'boolean',
                'category' => 'accounting',
                'is_group' => 0,
                'sort_order' => 31,
                'en' => 'Driver Ledger',
                'bn' => 'ড্রাইভার লেজার',
            ],
            [
                'key' => 'helper.ledger',
                'value_type' => 'boolean',
                'category' => 'accounting',
                'is_group' => 0,
                'sort_order' => 31,
                'en' => 'Helper Ledger',
                'bn' => 'হেল্পার লেজার',
            ],
            [
                'key' => 'supervisor.ledger',
                'value_type' => 'boolean',
                'category' => 'accounting',
                'is_group' => 0,
                'sort_order' => 31,
                'en' => 'Supervisor Ledger',
                'bn' => 'সুপারভাইজার লেজার',
            ],
            [
                'key' => 'employee.ledger',
                'value_type' => 'boolean',
                'category' => 'accounting',
                'is_group' => 0,
                'sort_order' => 31,
                'en' => 'Employee Ledger',
                'bn' => 'কর্মচারী লেজার',
            ],

            // ============================================
            // REPORTING & ANALYTICS FEATURES
            // ============================================
            [
                'key' => 'reports.basic',
                'value_type' => 'boolean',
                'category' => 'reports',
                'is_group' => 0,
                'sort_order' => 90,
                'en' => 'Basic Reports',
                'bn' => 'মৌলিক রিপোর্ট',
            ],
            [
                'key' => 'reports.profit_loss',
                'value_type' => 'boolean',
                'category' => 'reports',
                'is_group' => 1,
                'sort_order' => 91,
                'en' => 'Profit and Loss Statements',
                'bn' => 'লাভ ও ক্ষতির বিবরণী',
            ],
            [
                'key' => 'reports.vehicle_wise',
                'value_type' => 'boolean',
                'category' => 'reports',
                'is_group' => 0,
                'sort_order' => 92,
                'en' => 'Vehicle-wise Performance Reports',
                'bn' => 'যানবাহন ভিত্তিক কর্মক্ষমতা রিপোর্ট',
            ],
            [
                'key' => 'reports.advanced_analytics',
                'value_type' => 'boolean',
                'category' => 'reports',
                'is_group' => 1,
                'sort_order' => 93,
                'en' => 'Advanced Analytics and Business Intelligence',
                'bn' => 'উন্নত বিশ্লেষণ ও ব্যবসায়িক গোয়েন্দাগিরি',
            ],
            [
                'key' => 'reports.export',
                'value_type' => 'boolean',
                'category' => 'reports',
                'is_group' => 0,
                'sort_order' => 94,
                'en' => 'Report Export (PDF, Excel)',
                'bn' => 'রিপোর্ট রপ্তানি (PDF, Excel)',
            ],
            [
                'key' => 'finance.balance_cashflow_report',
                'value_type' => 'boolean',
                'category' => 'reports',
                'is_group' => 1,
                'sort_order' => 84,
                'en' => 'Balance Statement and Cash flow Report',
                'bn' => 'ব্যালেন্স স্টেটমেন্ট ও ক্যাশফ্লো রিপোর্ট',
            ],

            // ============================================
            // NOTIFICATION & INTEGRATION FEATURES
            // ============================================
            [
                'key' => 'notification.sms_email',
                'value_type' => 'boolean',
                'category' => 'communication',
                'is_group' => 1,
                'sort_order' => 110,
                'en' => 'SMS/Email Notification System',
                'bn' => 'SMS/Email নোটিফিকেশন সিস্টেম',
            ],

            [
                'key' => 'workflow.approval_verification',
                'value_type' => 'boolean',
                'category' => 'communication',
                'is_group' => 1,
                'sort_order' => 120,
                'en' => 'Approval and Verification System',
                'bn' => 'অ্যাপ্রুভাল ও ভেরিফাই সিস্টেম',
            ],
            [
                'key' => 'reservation.booking_management',
                'value_type' => 'boolean',
                'category' => 'communication',
                'is_group' => 1,
                'sort_order' => 121,
                'en' => 'Reservation and Booking Management',
                'bn' => 'রিজার্ভেশন ও বুকিং ব্যবস্থা',
            ],

            // ============================================
            // SUPPORT & ENTERPRISE FEATURES
            // ============================================
            [
                'key' => 'support.dedicated_account_manager',
                'value_type' => 'boolean',
                'category' => 'support',
                'is_group' => 1,
                'sort_order' => 130,
                'en' => 'Dedicated Account Manager and Live Support',
                'bn' => 'ডেডিকেটেড একাউন্ট ম্যানেজার ও লাইভ সাপোর্ট',
            ],
            [
                'key' => 'support.custom_integration',
                'value_type' => 'boolean',
                'category' => 'support',
                'is_group' => 1,
                'sort_order' => 132,
                'en' => 'Custom Integration and API Access',
                'bn' => 'কাস্টম ইন্টিগ্রেশন ও API অ্যাক্সেস',
            ],

            [
                'key' => 'support.settings',
                'value_type' => 'boolean',
                'category' => 'support',
                'is_group' => 1,
                'sort_order' => 133,
                'en' => 'Custom Settings',
                'bn' => 'কাস্টম সেটিংস',
            ],

            // ============================================
            // DATA & FILE MANAGEMENT FEATURES
            // ============================================
            [
                'key' => 'data.file_management',
                'value_type' => 'boolean',
                'category' => 'data_management',
                'is_group' => 1,
                'sort_order' => 140,
                'en' => 'Advanced File Management and Storage',
                'bn' => 'উন্নত ফাইল ব্যবস্থাপনা ও স্টোরেজ',
            ],
            [
                'key' => 'data.backup_retention',
                'value_type' => 'boolean',
                'category' => 'data_management',
                'is_group' => 1,
                'sort_order' => 141,
                'en' => 'Extended Data Backup and Retention',
                'bn' => 'বর্ধিত ডেটা ব্যাকআপ এবং ধারণক্ষমতা',
            ],
            [
                'key' => 'data.export_import',
                'value_type' => 'boolean',
                'category' => 'data_management',
                'is_group' => 0,
                'sort_order' => 142,
                'en' => 'Bulk Data Export and Import',
                'bn' => 'বাল্ক ডেটা রপ্তানি ও আমদানি',
            ],
        ];

        foreach ($features as $row) {
            $feature = Feature::query()->updateOrCreate(
                ['key' => $row['key']],
                [
                    'name' => $row['en'],
                    'description' => $row['category'] ?? 'General feature',
                    'group' => $row['category'],
                    'value_type' => $row['value_type'],
                    'is_group' => $row['is_group'],
                    'is_active' => 1,
                ]
            );

            // Create English translation
            if (isset($languageIds['en'])) {
                FeatureTranslation::query()->updateOrCreate(
                    [
                        'feature_id' => $feature->id,
                        'language_id' => (int) $languageIds['en'],
                    ],
                    [
                        'name' => $row['en'],
                        'description' => $row['category'] ?? 'General feature',
                    ]
                );
            }

            // Create Bengali translation
            if (isset($languageIds['bn'])) {
                FeatureTranslation::query()->updateOrCreate(
                    [
                        'feature_id' => $feature->id,
                        'language_id' => (int) $languageIds['bn'],
                    ],
                    [
                        'name' => $row['bn'],
                        'description' => $row['category'] ?? 'সাধারণ ফিচার',
                    ]
                );
            }
        }

        $this->command->info('Successfully seeded ' . count($features) . ' features with translations.');
    }
}
