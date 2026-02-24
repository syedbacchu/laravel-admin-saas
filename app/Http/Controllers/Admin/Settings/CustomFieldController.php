<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomField\CustomFieldCreateRequest;
use App\Http\Services\CustomField\CustomFieldServiceInterface;
use App\Http\Services\Response\ModelScannerService;
use App\Http\Services\Response\ResponseService;
use Illuminate\Http\Request;

class CustomFieldController extends Controller
{
    protected CustomFieldServiceInterface $service;

    public function __construct(CustomFieldServiceInterface $service)
    {
        $this->service = $service;
    }
    public function index() {
        $data = $this->service->getModuleData()['data'];
        $data['pageTitle'] = __('Custom Fields');

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('custom','index'));
    }

    public function listByModule(Request $request)
    {
        return $this->service->getByModule($request->module);
    }


    public function store(CustomFieldCreateRequest $request) {
        $response = $this->service->storeOrUpdateItem($request);
        return $response;
    }

    public function update(CustomFieldCreateRequest $request) {
        $response = $this->service->storeOrUpdateItem($request);
        return $response;
    }
    public function destroy($id) {
        $response = $this->service->deleteItem($id);
        return $response;
    }
}
