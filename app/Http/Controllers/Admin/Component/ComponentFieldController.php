<?php

namespace App\Http\Controllers\Admin\Component;

use App\Http\Controllers\Controller;
use App\Enums\FieldTypeEnum;
use App\Http\Services\ComponentField\ComponentFieldServiceInterface;
use App\Http\Services\Response\ResponseService;
use App\Support\DataListManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ComponentFieldController extends Controller
{
    protected ComponentFieldServiceInterface $componentFieldService;

    public function __construct(ComponentFieldServiceInterface $componentFieldService)
    {
        $this->componentFieldService = $componentFieldService;
    }

    public function index(Request $request, string $component)
    {
        // This will redirect to the component fields management page
        return redirect()->route('component.fields', $component);
    }

    public function create(Request $request, string $component)
    {
        $response = app(\App\Http\Services\Component\ComponentServiceInterface::class)->getComponentWithFields($component);
        if (!$response) {
            return ResponseService::send();
        }

        $componentModel = $response;

        $data['pageTitle'] = __('Create Field for: ') . $componentModel->name;
        $data['function_type'] = 'create';
        $data['componentModel'] = $componentModel;
        $data['field_types'] = $this->getFieldTypes();
        $data['parent_fields'] = $this->componentFieldService->getParentFields($componentModel->id)['data'] ?? collect();

        return view(viewss('component', 'field_create'), $data);
    }

    public function store(Request $request, string $component): RedirectResponse
    {
        $response = $this->componentFieldService->storeOrUpdateField($request, (int)$component);
        return ResponseService::send([
            'response' => $response,
        ], null, null, ['id' => $component], 'component.fields');
    }

    public function show(string $component, string $id)
    {
        $response = $this->componentFieldService->getFieldDetail((int)$id);
        if (!$response) {
            return ResponseService::send();
        }

        $componentModel = app(\App\Http\Services\Component\ComponentServiceInterface::class)->getComponentWithFields($component);
        $field = $response;

        $data['pageTitle'] = __('Field Details');
        $data['componentModel'] = $componentModel;
        $data['field'] = $field;
        $data['function_type'] = 'view';

        return view(viewss('component', 'field_show'), $data);
    }

    public function edit(Request $request, string $component, string $id)
    {
        $componentResponse = app(\App\Http\Services\Component\ComponentServiceInterface::class)->getComponentWithFields($component);
        if (!$componentResponse) {
            return ResponseService::send();
        }

        $fieldResponse = $this->componentFieldService->getFieldDetail((int)$id);
        if (!$fieldResponse) {
            return ResponseService::send();
        }

        $componentModel = $componentResponse;
        $field = $fieldResponse;

        $data['pageTitle'] = __('Update Field');
        $data['function_type'] = 'update';
        $data['componentModel'] = $componentModel;
        $data['item'] = $field;
        $data['field_types'] = $this->getFieldTypes();
        $data['parent_fields'] = $this->componentFieldService->getParentFields($componentModel->id)['data'] ?? collect();

        return view(viewss('component', 'field_create'), $data);
    }

    public function update(Request $request, string $component, string $id): RedirectResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->componentFieldService->storeOrUpdateField($request, (int)$component);

        return ResponseService::send([
            'response' => $response,
        ], null, null, ['id' => $component], 'component.fields');
    }

    public function destroy(string $component, string $id): RedirectResponse
    {
        $response = $this->componentFieldService->deleteField((int)$id);
        return ResponseService::send([
            'response' => $response,
        ], null, null, ['id' => $component], 'component.fields');
    }

    public function updateSortOrder(Request $request): JsonResponse
    {
        try {
            $response = $this->componentFieldService->updateFieldSortOrder($request);
            return response()->json($response);
        } catch (\Exception $e) {
            logStore('fieldUpdateSortOrder', $e->getMessage());
            return response()->json(['success' => false, 'message' => somethingWrong()]);
        }
    }

    public function getChildren(Request $request, string $component, string $parentId)
    {
        $field = $this->componentFieldService->getFieldDetail((int)$parentId);
        if (!$field || $field->field_type !== 'repeatable') {
            return response()->json(['success' => false, 'message' => __('Invalid parent field')]);
        }

        $children = $field->children()->orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'data' => $children
        ]);
    }

    private function getFieldTypes(): array
    {
        return FieldTypeEnum::toSelectArray();
    }
}