<?php

namespace Database\Seeders;

use App\Enums\SettingGroupEnum;
use App\Models\AdminSettings;
use Illuminate\Database\Seeder;

class AdminSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Email Setting
        AdminSettings::firstOrCreate(['slug' => 'mail_driver'], ['group' => enum(SettingGroupEnum::SETTING_GROUP_MAIL) ,'value' => 'SMTP']);
        AdminSettings::firstOrCreate(["slug" => "email_host"],      ['group' => enum(SettingGroupEnum::SETTING_GROUP_MAIL) ,"value" => ""]);
        AdminSettings::firstOrCreate(["slug" => "email_port"],      ['group' => enum(SettingGroupEnum::SETTING_GROUP_MAIL) ,"value" => ""]);
        AdminSettings::firstOrCreate(["slug" => "email_username"],  ['group' => enum(SettingGroupEnum::SETTING_GROUP_MAIL) ,"value" => ""]);
        AdminSettings::firstOrCreate(["slug" => "email_password"],  ['group' => enum(SettingGroupEnum::SETTING_GROUP_MAIL) ,"value" => ""]);
        AdminSettings::firstOrCreate(["slug" => "email_encryption"],['group' => enum(SettingGroupEnum::SETTING_GROUP_MAIL) ,"value" => ""]);
        AdminSettings::firstOrCreate(["slug" => "mail_from_address"],['group' => enum(SettingGroupEnum::SETTING_GROUP_MAIL) ,"value" => ""]);

        // general settings
        AdminSettings::firstOrCreate(['slug'=>'app_title'],['group' => enum(SettingGroupEnum::SETTING_GROUP_GENERAL),'value'=>'Laravel Admin']);
        AdminSettings::firstOrCreate(['slug'=>'tag_title'],['group' => enum(SettingGroupEnum::SETTING_GROUP_GENERAL),'value'=>'']);
        AdminSettings::firstOrCreate(['slug'=>'company_email'],['group' => enum(SettingGroupEnum::SETTING_GROUP_GENERAL),'value'=>'info@laravel.com']);
        AdminSettings::firstOrCreate(['slug'=>'company_address'],['group' => enum(SettingGroupEnum::SETTING_GROUP_GENERAL),'value'=>'']);
        AdminSettings::firstOrCreate(['slug'=>'helpline'],['group' => enum(SettingGroupEnum::SETTING_GROUP_GENERAL),'value'=>'']);

        AdminSettings::firstOrCreate(['slug' => 'logo'],['group' => enum(SettingGroupEnum::SETTING_GROUP_LOGO),'value' => '']);
        AdminSettings::firstOrCreate(['slug' => 'login_logo'],['group' => enum(SettingGroupEnum::SETTING_GROUP_LOGO),'value' => '']);
        AdminSettings::firstOrCreate(['slug' => 'login_sidebar_image'],['group' => enum(SettingGroupEnum::SETTING_GROUP_LOGO),'value' => '']);
        AdminSettings::firstOrCreate(['slug' => 'favicon'], ['group' => enum(SettingGroupEnum::SETTING_GROUP_LOGO),'value' => '']);

        AdminSettings::firstOrCreate(['slug' => 'copyright_text'], ['group' => enum(SettingGroupEnum::SETTING_GROUP_GENERAL),'value' => 'Copyright@2025']);
        AdminSettings::firstOrCreate(['slug' => 'pagination_count'], ['group' => enum(SettingGroupEnum::SETTING_GROUP_GENERAL),'value' => '10']);
        AdminSettings::firstOrCreate(['slug' => 'currency'],['group' => enum(SettingGroupEnum::SETTING_GROUP_GENERAL), 'value' => 'USD']);
        AdminSettings::firstOrCreate(['slug' => 'lang'], ['group' => enum(SettingGroupEnum::SETTING_GROUP_GENERAL),'value' => 'en']);
    }
}
