<?php

namespace App\Http\Controllers\Admin\Component;

use App\Http\Controllers\Controller;
use App\Enums\FieldTypeEnum;
use App\Http\Services\Component\ComponentServiceInterface;
use App\Http\Services\Response\ResponseService;
use App\Support\DataListManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ComponentController extends Controller
{
    protected ComponentServiceInterface $componentService;

    public function __construct(ComponentServiceInterface $componentService)
    {
        $this->componentService = $componentService;
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __('Component List');
        if ($request->ajax()) {
            return DataListManager::dataTableHandle(
                request: $request,
                dataProvider: function ($request) {
                    return $this->componentService
                        ->getDataTableData($request)['data']['data'];
                },
                columns: [
                    'name' => fn ($item) => $item->name,

                    'slug' => fn ($item) => '<span class="font-mono text-sm">' . $item->slug . '</span>',

                    'fields_count' => fn ($item) => $item->fields_count ?? $item->fields->count() . ' ' . __('fields'),

                    'status_toggle' => fn ($item) =>
                    toggle_column(
                        route('component.publish'),
                        $item->id,
                        $item->status == 1
                    ),

                    'actions' => function ($item) {
                        $buttons = [
                            edit_column(route('component.edit', $item->id)),
                            field_button(route('component.fields', $item->id)),
                            delete_column(route('component.delete', $item->id)),
                        ];

                        return action_buttons($buttons);
                    },
                ],
                rawColumns: ['name', 'slug', 'fields_count', 'status_toggle', 'actions']
            );
        }

        return view(viewss('component', 'list'), $data);
    }

    public function create(Request $request)
    {
        $data['pageTitle'] = __('Create Component');
        $data['function_type'] = 'create';
        $data['field_types'] = $this->getFieldTypes();

        return view(viewss('component', 'create'), $data);
    }

    public function store(Request $request): RedirectResponse
    {
        $response = $this->componentService->storeOrUpdateComponent($request);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'component.list');
    }

    public function show(string $id)
    {
        $response = $this->componentService->getComponentWithFields($id);
        if (!$response) {
            return ResponseService::send();
        }

        $data['pageTitle'] = __('Component Details');
        $data['componentModel'] = $response;
        $data['function_type'] = 'view';

        return view(viewss('component', 'show'), $data);
    }

    public function edit(string $id)
    {
        $response = $this->componentService->getComponentWithFields($id);
        if (!$response) {
            return ResponseService::send();
        }

        $componentModel = $response;
        $data['pageTitle'] = __('Update Component');
        $data['function_type'] = 'update';
        $data['item'] = $componentModel;
        $data['field_types'] = $this->getFieldTypes();

        return view(viewss('component', 'create'), $data);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->componentService->storeOrUpdateComponent($request);

        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'component.list');
    }

    public function destroy(string $id): RedirectResponse
    {
        $response = $this->componentService->deleteComponent($id);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'component.list');
    }

    public function publishComponent(Request $request): JsonResponse
    {
        try {
            $response = $this->componentService->publishComponent($request->id, $request->status);
            return response()->json($response);
        } catch (\Exception $e) {
            logStore('componentPublishStatus', $e->getMessage());
            return response()->json(['success' => false, 'message' => somethingWrong()]);
        }
    }

    public function fields(Request $request, string $id)
    {
        $response = $this->componentService->getComponentWithFields($id);
        if (!$response) {
            return ResponseService::send();
        }

        $componentModel = $response;
        $pageTitle = __('Manage Fields: ') . $componentModel->name;
        $field_types = $this->getFieldTypes();

        if ($request->ajax()) {
            $componentId = $componentModel->id;
            return DataListManager::dataTableHandle(
                request: $request,
                dataProvider: function ($request) use ($componentId) {
                    $request->merge(['component_id' => $componentId]);
                    return app(\App\Http\Services\ComponentField\ComponentFieldServiceInterface::class)
                        ->getDataTableData($request, $componentId)['data']['data'];
                },
                columns: [
                    'name' => fn ($item) => $item->name,
                    'label' => fn ($item) => $item->label,
                    'field_type' => function ($item) {
                        $fieldType = \App\Enums\FieldTypeEnum::tryFrom($item->field_type);
                        $label = $fieldType ? $fieldType->getLabel() : $item->field_type;
                        return '<span class="inline-flex items-center px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs font-medium">
                            ' . $label . '
                        </span>';
                    },
                    'is_required' => fn ($item) => $item->is_required
                        ? '<span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            ' . __('Yes') . '
                           </span>'
                        : '<span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">' . __('No') . '</span>',
                    'is_translatable' => fn ($item) => $item->is_translatable
                        ? '<span class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M7 4v16M7 4h4m-4 0h4M7 8h4m-4 0h4" stroke-width="2" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            ' . __('Yes') . '
                           </span>'
                        : '<span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">' . __('No') . '</span>',
                    'parent' => fn ($item) => $item->parent ? '<small>' . $item->parent->label . '</small>' : '-',
                    'actions' => function ($item) use ($componentId) {
                        $buttons = [
                            edit_column(route('component.field.edit', ['component' => $componentId, 'field' => $item->id])),
                            delete_column(route('component.field.delete', ['component' => $componentId, 'field' => $item->id])),
                        ];

                        return action_buttons($buttons);
                    },
                ],
                rawColumns: ['name', 'label', 'field_type', 'is_required', 'is_translatable', 'parent', 'actions']
            );
        }

        return view(viewss('component', 'fields'), compact('pageTitle', 'componentModel', 'field_types'));
    }

    private function getFieldTypes(): array
    {
        return FieldTypeEnum::toSelectArray();
    }
}
