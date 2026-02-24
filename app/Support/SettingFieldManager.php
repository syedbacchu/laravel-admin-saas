<?php


namespace App\Support;

use App\Models\SettingsField;
use Exception;
use Illuminate\Support\Str;

class SettingFieldManager
{
    /**
     * Get group-wise settings
     */
    public static function fieldList(): mixed
    {
        return SettingsField::orderBy('group')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group');
    }


    /**
     * Save multiple settings (supports file upload)
     */
    public static function save($request): array
    {
        try {
            $data = [
                'group' => Str::slug($request->group),
                'label' => $request->label,
                'slug'  => Str::slug($request->slug, '_'),
                'type'  => $request->type,
                'validation_rules' => $request->validation_rules,
            ];

            if (in_array($request->type, ['select', 'radio','checkbox'])) {
                $data['options'] = array_map('trim', explode(',', $request->options));
            } else {
                $data['options'] = null;
            }
            SettingsField::create($data);
            return sendResponse(true,__('Settings field created successfully'));

        } catch (Exception $e) {
            return sendResponse(false,$e->getMessage());
        }
    }


    public static function updateData($request, $field): array {
        try {
            $data = [
                'group' => Str::slug($request->group),
                'label' => $request->label,
                'slug'  => Str::slug($request->slug, '_'),
                'type'  => $request->type,
                'validation_rules' => $request->validation_rules,
            ];

            if (in_array($request->type, ['select', 'radio','checkbox'])) {
                $data['options'] = array_map('trim', explode(',', $request->options));
            } else {
                $data['options'] = null;
            }
            $field->update($data);

            return sendResponse(true,__('Settings field updated successfully'));
        } catch (\Exception $e) {
            logStore('updateData ex', $e->getMessage());
            return sendResponse(false, $e->getMessage());
        }
    }

    public static function delete(SettingsField $field): array {
        try {
            $setting = Settings::get($field->slug);
            if(!empty($setting)) {
                return sendResponse(false,$field->label.__(' already have some value. Please clear it first from settings'));
            }
            $field->delete();
            return sendResponse(true,__('Settings field deleted successfully'));
        } catch (\Exception $e) {
            return sendResponse(false, $e->getMessage());
        }
    }
}
