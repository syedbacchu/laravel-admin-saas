<?php

namespace App\Http\Controllers\Admin\Language;

use App\Http\Controllers\Controller;
use App\Http\Requests\Language\LanguageCreateRequest;
use App\Http\Services\Language\LanguageServiceInterface;
use App\Http\Services\Response\ResponseService;
use App\Support\DataListManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    protected LanguageServiceInterface $service;

    public function __construct(LanguageServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __('Language List');
        if ($request->ajax()) {
            return DataListManager::dataTableHandle(
                request: $request,
                dataProvider: function ($request) {
                    return $this->service->getDataTableData($request)['data']['data'];
                },
                columns: [
                    'display_name' => function ($item) {
                        $native = $item->native_name ? '<small class="text-gray-500">' . e($item->native_name) . '</small>' : '';
                        return '<div class="flex flex-col"><span class="font-semibold text-gray-900">' . e($item->name) . '</span>' . $native . '</div>';
                    },
                    'status_badge' => function ($item) {
                        if ((int) $item->is_default === 1) {
                            return '<span class="badge bg-success">Default</span>';
                        }

                        return toggle_column(
                            route('language.publish'),
                            (int) $item->id,
                            (int) $item->status === 1
                        );
                    },
                    'actions' => fn ($item) =>
                        action_buttons(array_filter([
                            edit_column(route('language.edit', $item->id)),
                            ((int) $item->is_default === 1 || $item->code === 'en') ? null : delete_column(route('language.delete', $item->id)),
                        ])),
                ],
                rawColumns: ['display_name', 'status_badge', 'actions']
            );
        }

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('language', 'list'));
    }

    public function create(Request $request)
    {
        $data['pageTitle'] = __('Create Language');
        $data['function_type'] = 'create';

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('language', 'create'));
    }

    public function store(LanguageCreateRequest $request): RedirectResponse
    {
        $response = $this->service->storeOrUpdateLanguage($request);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'language.list');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $response = $this->service->languageEditData($id);
        if ($response['success'] === false) {
            return ResponseService::send();
        }

        $data['pageTitle'] = __('Update Language');
        $data['function_type'] = 'update';
        $data['item'] = $response['data'];

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('language', 'create'));
    }

    public function update(LanguageCreateRequest $request, string $id): RedirectResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->service->storeOrUpdateLanguage($request);

        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'language.list');
    }

    public function destroy(string $id): RedirectResponse
    {
        $response = $this->service->deleteLanguage($id);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'language.list');
    }

    public function languageStatus(Request $request): JsonResponse
    {
        try {
            $response = $this->service->publishLanguage($request->id, $request->status);
            return response()->json($response);
        } catch (\Throwable $e) {
            logStore('languageStatus', $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => somethingWrong(),
            ]);
        }
    }
}

