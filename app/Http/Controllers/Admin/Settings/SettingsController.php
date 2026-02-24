<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Services\Response\ResponseService;
use App\Support\Settings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{

    public function index(Request $request)
    {
        $data = Settings::createData($request)['data'];
        $data['pageTitle'] = __('Settings');
        return ResponseService::send([
            'data' => $data,
        ], view: viewss('settings','index'));
    }

    public function update(Request $request, string $group)
    {
        $response = Settings::updateData($request, $group);
        return ResponseService::send([
            'response' => $response,
        ],'settings.generalSetting', null,
            ['group' => $group],'settings.generalSetting');

    }

}





