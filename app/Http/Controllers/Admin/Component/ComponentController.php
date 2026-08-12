<?php

namespace App\Http\Controllers\Admin\Component;

use App\Http\Controllers\Controller;
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
                        $item->status === 1
                    ),

                    'actions' => function ($item) {
                        $buttons = [
                            edit_column(route('component.edit', $item->id)),
                            '<a href="' . route('component.fields', $item->id) . '" class="btn btn-sm btn-info"><i class="fas fa-list"></i></a>',
                            delete_column(route('component.delete', $item->id)),
                        ];

                        return action_buttons($buttons);
                    },
                ],
                rawColumns: ['name', 'slug', 'fields_count', 'status_toggle', 'actions']
            );
        }

        return ResponseService::send([
            'data' => $data,
        ], null, viewss('component', 'list'));
    }

    public function create(Request $request)
    {
        $data['pageTitle'] = __('Create Component');
        $data['function_type'] = 'create';
        $data['field_types'] = $this->getFieldTypes();

        return ResponseService::send([
            'data' => $data,
        ], null, viewss('component', 'create'));
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
        $data['component'] = $response;
        $data['function_type'] = 'view';

        return ResponseService::send([
            'data' => $data,
        ], null, viewss('component', 'show'));
    }

    public function edit(string $id)
    {
        $response = $this->componentService->getComponentWithFields($id);
        if (!$response) {
            return ResponseService::send();
        }

        $component = $response;
        $data['pageTitle'] = __('Update Component');
        $data['function_type'] = 'update';
        $data['item'] = $component;
        $data['field_types'] = $this->getFieldTypes();

        return ResponseService::send([
            'data' => $data,
        ], null, viewss('component', 'create'));
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

        $component = $response;
        $data['pageTitle'] = __('Manage Fields: ') . $component->name;
        $data['component'] = $component;
        $data['field_types'] = $this->getFieldTypes();

        if ($request->ajax()) {
            $componentId = $component->id;
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
                    'field_type' => fn ($item) => '<span class="badge badge-info">' . $item->field_type . '</span>',
                    'is_required' => fn ($item) => $item->is_required ? '<i class="fas fa-check text-success"></i>' : '-',
                    'is_translatable' => fn ($item) => $item->is_translatable ? '<i class="fas fa-language text-primary"></i>' : '-',
                    'parent' => fn ($item) => $item->parent ? '<small>' . $item->parent->label . '</small>' : '-',
                    'actions' => function ($item) {
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

        return ResponseService::send([
            'data' => $data,
        ], null, viewss('component', 'fields'));
    }

    private function getFieldTypes(): array
    {
        return [
            'text' => __('Text'),
            'textarea' => __('Textarea'),
            'richtext' => __('Rich Text'),
            'number' => __('Number'),
            'boolean' => __('Boolean'),
            'url' => __('URL'),
            'image' => __('Image'),
            'responsive_image' => __('Responsive Image'),
            'select' => __('Select'),
            'relation' => __('Relation'),
            'repeatable' => __('Repeatable'),
            'video' => __('Video'),
            'file' => __('File'),
            'date' => __('Date'),
            'datetime' => __('DateTime'),
            'color' => __('Color'),
            'group' => __('Group'),
        ];
    }
}