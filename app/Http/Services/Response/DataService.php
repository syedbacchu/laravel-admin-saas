<?php

namespace App\Http\Services\Response;

use Illuminate\Support\Facades\Hash;

class DataService
{
    public static function userCreateData($request): array
    {
        $data = [];
        if (!empty($request->name)) {
            $data['name'] = $request->name;
        }
        if (!empty($request->phone)) {
            $data['phone'] = $request->phone;
        }
        if (!empty($request->username)) {
            $data['username'] = $request->username;
        }
        if (!empty($request->email)) {
            $data['email'] = $request->email;
        }
        if (!empty($request->phone_code)) {
            $data['phone_code'] = $request->phone_code;
        }
        if (!empty($request->password)) {
            $data['password'] = Hash::make($request->password);
        }
        if (!empty($request->role_module)) {
            $data['role_module'] = $request->role_module;
        }
        if (!empty($request->role_id)) {
            $data['role_id'] = $request->role_id;
        }
        if (!empty($request->status)) {
            $data['status'] = $request->status;
        }
        if (!empty($request->is_private)) {
            $data['is_private'] = $request->is_private;
        }
        if (!empty($request->is_phone_verified)) {
            $data['is_phone_verified'] = $request->is_phone_verified;
        }
        if (!empty($request->is_email_verified)) {
            $data['is_email_verified'] = $request->is_email_verified;
        }
        if (!empty($request->image)) {
            $data['image'] = $request->image;
        }
        if (!empty($request->gender)) {
            $data['gender'] = $request->gender;
        }
        if (!empty($request->date_of_birth)) {
            $data['date_of_birth'] = $request->date_of_birth;
        }
        if (!empty($request->blood_group)) {
            $data['blood_group'] = $request->blood_group;
        }
        if (!empty($request->language)) {
            $data['language'] = $request->language;
        }
        if (!empty($request->address)) {
            $data['address'] = $request->address;
        }
        if (!empty($request->country)) {
            $data['country'] = $request->country;
        }
        if (!empty($request->division)) {
            $data['division'] = $request->division;
        }
        if (!empty($request->thana)) {
            $data['thana'] = $request->thana;
        }
        if (!empty($request->city)) {
            $data['city'] = $request->city;
        }
        if (!empty($request->postal_code)) {
            $data['postal_code'] = $request->postal_code;
        }
        if (!empty($request->email_notification_status)) {
            $data['email_notification_status'] = $request->email_notification_status;
        }
        if (!empty($request->phone_notification_status)) {
            $data['phone_notification_status'] = $request->phone_notification_status;
        }
        if (!empty($request->push_notification_status)) {
            $data['push_notification_status'] = $request->push_notification_status;
        }
        if (!empty($request->facebook_link)) {
            $data['facebook_link'] = $request->facebook_link;
        }
        if (!empty($request->linkedin_link)) {
            $data['linkedin_link'] = $request->linkedin_link;
        }
        if (!empty($request->youtube_link)) {
            $data['youtube_link'] = $request->youtube_link;
        }
        if (!empty($request->twitter_link)) {
            $data['twitter_link'] = $request->twitter_link;
        }
        if (!empty($request->instagram_link)) {
            $data['instagram_link'] = $request->instagram_link;
        }
        if (!empty($request->whatsapp_link)) {
            $data['whatsapp_link'] = $request->whatsapp_link;
        }
        if (!empty($request->telegram_link)) {
            $data['telegram_link'] = $request->telegram_link;
        }
        return $data;
    }
}
