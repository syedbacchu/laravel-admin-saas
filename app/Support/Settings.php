<?php


namespace App\Support;

use App\Enums\FileDestinationEnum;
use App\Models\AdminSettings;
use App\Models\SettingsField;
use Illuminate\Support\Facades\Cache;
use Exception;

class Settings
{
    protected static string $cacheKey = 'app_settings';

    /**
     * Get all settings (cached)
     */
    public static function all(): array
    {
        return Cache::rememberForever(self::$cacheKey, function () {
            return AdminSettings::pluck('value', 'slug')->toArray();
        });
    }

    /**
     * Get group-wise settings
     */
    public static function group(string $group): array
    {
        return AdminSettings::where('group', $group)
            ->pluck('value', 'slug')
            ->toArray();
    }

    /**
     * Get a single setting
     */
    public static function get(string $key, $default = null)
    {
        return self::all()[$key] ?? $default;
    }

    /**
     * Set a single setting
     */
    public static function set(string $key, $value, string $group = 'general'): void
    {
        AdminSettings::updateOrCreate(
            ['slug' => $key],
            ['value' => $value, 'group' => $group]
        );

        self::clearCache();
    }

    /**
     * Save multiple settings (supports file upload)
     */
    public static function save($request, string $group = 'general'): array
    {
        try {
            if (is_array($request)) {
                $data = $request;
            } else {
                // Laravel Request object
                $data = $request->except('_token');
            }

            foreach ($data as $key => $value) {
                $valueData = $value;

                // Only check for file uploads if we have a Request object
                if (!is_array($request) && $request->hasFile($key)) {
                    $settingImage = uploadImageFileInStorage($request->$key,enum(FileDestinationEnum::GENERAL_IMAGE_PATH));
                    if($settingImage['success'] == false) {
                        return $settingImage;
                    }
                    $valueData  = $settingImage['data']['file_name'];
                }

                AdminSettings::updateOrCreate(["slug" => $key],["value" => $valueData, "group" => $group]);
            }

            self::clearCache();
            return sendResponse(true,__('Settings updated successfully'));

        } catch (Exception $e) {
            self::clearCache();
            return sendResponse(false,$e->getMessage());
        }
    }

    /**
     * Clear settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget(self::$cacheKey);
    }

    public static function createData($request): array
    {
        $data['groups'] = SettingsField::where('status', 1)
            ->orderBy('group')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group');

        $data['activeTab'] = $request->get('group') ?? $data['groups']->keys()->first();

        $data['values'] = AdminSettings::pluck('value', 'slug');
        return sendResponse(true,__('Settings get successfully'), $data);
    }

    public static function updateData($request, $group): array {
        try {
            $fields = SettingsField::where('group', $group)
                ->where('status', 1)
                ->get();

            $rules = [];

            foreach ($fields as $field) {
                if ($field->validation_rules) {
                    $rules[$field->slug] = $field->validation_rules;
                }
            }

            $validated = $request->validate($rules);

            foreach ($fields as $field) {

                // CHECKBOX (array)
                if ($field->type === 'checkbox') {
                    $value = json_encode($request->input($field->slug, []));
                } else {
                    $value = $request->input($field->slug);
                }

                AdminSettings::updateOrCreate(
                    ['slug' => $field->slug],
                    [
                        'group' => $group,
                        'value' => $value,
                    ]
                );
            }

            return sendResponse(true,__('Settings updated successfully'));
        } catch (\Exception $e) {
            logStore('updateData ex', $e->getMessage());
            return sendResponse(false, $e->getMessage());
        }
    }
}
