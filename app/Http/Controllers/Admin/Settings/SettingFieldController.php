<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\FieldCreateRequest;
use App\Http\Requests\Settings\FieldUpdateRequest;
use App\Http\Services\Response\ResponseService;
use App\Models\SettingsField;
use App\Support\SettingFieldManager;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingFieldController extends Controller
{
    public function index()
    {
        $data['fields'] = SettingFieldManager::fieldList();
        $data['pageTitle'] = __('Settings Fields');

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('settings','fields'));
    }

    public function create()
    {
        $data['pageTitle'] = __('Settings Fields');
        return ResponseService::send([
            'data' => $data,
        ], view: viewss('settings','field'));
    }

    public function store(FieldCreateRequest $request)
    {
        $response = SettingFieldManager::save($request);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'settings.fields.index');
    }
    public function edit(SettingsField $field)
    {
        $data['pageTitle'] = __('Update Fields');
        $data['field'] = $field;

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('settings','field-edit'));
    }

    public function update(FieldUpdateRequest $request, SettingsField $field)
    {
        $response = SettingFieldManager::updateData($request, $field);
        return ResponseService::send([
            'response' => $response,
        ]);

    }

    public function destroy(SettingsField $field)
    {
        $response = SettingFieldManager::delete($field);
        return ResponseService::send([
            'response' => $response,
        ]);
    }
}
