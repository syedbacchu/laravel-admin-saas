<?php

namespace Database\Seeders;

use App\Enums\SettingGroupEnum;
use App\Models\SettingsField;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SettingsField::firstOrCreate(['slug' => 'mail_driver'],['group' => enum(SettingGroupEnum::SETTING_GROUP_MAIL), 'label' => 'Mail Driver' ]);
        SettingsField::firstOrCreate(['slug' => 'email_host'],['group' => enum(SettingGroupEnum::SETTING_GROUP_MAIL), 'label' => 'Mail Host' ]);
        SettingsField::firstOrCreate(['slug' => 'email_port'],['group' => enum(SettingGroupEnum::SETTING_GROUP_MAIL), 'label' => 'Mail Port' ]);
        SettingsField::firstOrCreate(['slug' => 'email_username'],['group' => enum(SettingGroupEnum::SETTING_GROUP_MAIL), 'label' => 'Mail Username' ]);
        SettingsField::firstOrCreate(['slug' => 'email_password'],['group' => enum(SettingGroupEnum::SETTING_GROUP_MAIL), 'label' => 'Mail Password', 'type' => 'password' ]);
        SettingsField::firstOrCreate(['slug' => 'email_encryption'],['group' => enum(SettingGroupEnum::SETTING_GROUP_MAIL), 'label' => 'Mail Encryption' ]);
        SettingsField::firstOrCreate(['slug' => 'mail_from_address'],['group' => enum(SettingGroupEnum::SETTING_GROUP_MAIL), 'label' => 'Mail From Address' ]);

        SettingsField::firstOrCreate(['slug' => 'app_title'],['group' => enum(SettingGroupEnum::SETTING_GROUP_GENERAL), 'label' => 'App Title' ]);
        SettingsField::firstOrCreate(['slug' => 'tag_title'],['group' => enum(SettingGroupEnum::SETTING_GROUP_GENERAL), 'label' => 'Tag Title' ]);
        SettingsField::firstOrCreate(['slug' => 'company_email'],['group' => enum(SettingGroupEnum::SETTING_GROUP_GENERAL), 'label' => 'Company Email' ]);
        SettingsField::firstOrCreate(['slug' => 'company_address'],['group' => enum(SettingGroupEnum::SETTING_GROUP_GENERAL), 'label' => 'Company Address' ]);
        SettingsField::firstOrCreate(['slug' => 'helpline'],['group' => enum(SettingGroupEnum::SETTING_GROUP_GENERAL), 'label' => 'Helpline' ]);
        SettingsField::firstOrCreate(['slug' => 'copyright_text'],['group' => enum(SettingGroupEnum::SETTING_GROUP_GENERAL), 'label' => 'Copyright Text' ]);
        SettingsField::firstOrCreate(['slug' => 'pagination_count'],['group' => enum(SettingGroupEnum::SETTING_GROUP_GENERAL), 'label' => 'pagination_count' ]);
        SettingsField::firstOrCreate(['slug' => 'currency'],['group' => enum(SettingGroupEnum::SETTING_GROUP_GENERAL), 'label' => 'currency' ]);
        SettingsField::firstOrCreate(['slug' => 'lang'],['group' => enum(SettingGroupEnum::SETTING_GROUP_GENERAL), 'label' => 'Language' ]);

        SettingsField::firstOrCreate(['slug' => 'logo'],['group' => enum(SettingGroupEnum::SETTING_GROUP_LOGO), 'label' => 'Logo', 'type' => 'file']);
        SettingsField::firstOrCreate(['slug' => 'login_logo'],['group' => enum(SettingGroupEnum::SETTING_GROUP_LOGO), 'label' => 'Login Logo', 'type' => 'file' ]);
        SettingsField::firstOrCreate(['slug' => 'login_sidebar_image'],['group' => enum(SettingGroupEnum::SETTING_GROUP_LOGO), 'label' => 'Login Sidebar Image', 'type' => 'file' ]);
        SettingsField::firstOrCreate(['slug' => 'favicon'],['group' => enum(SettingGroupEnum::SETTING_GROUP_LOGO), 'label' => 'Favicon', 'type' => 'file' ]);
    }
}
