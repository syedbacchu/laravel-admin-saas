<?php

namespace App\Http\Controllers\Admin\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\PostCategoryCreateRequest;
use App\Http\Services\PostCategory\PostCategoryServiceInterface;
use App\Http\Services\Response\ResponseService;
use App\Support\DataListManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostCategoryController extends Controller
{
    protected PostCategoryServiceInterface $service;

    public function __construct(PostCategoryServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __('Post Category List');
        if ($request->ajax()) {
            return DataListManager::dataTableHandle(
                request: $request,
                dataProvider: function ($request) {
                    return $this->service
                        ->getDataTableData($request)['data']['data'];
                },
                columns: [
                    'parent_name' => fn ($item) => $item->parent?->name ?? '-',

                    'status' => fn ($item) =>
                    toggle_column(
                        route('postCategory.publish'),
                        $item->id,
                        $item->status == 1
                    ),

                    'actions' => fn ($item) =>
                    action_buttons([
                        edit_column(route('postCategory.edit', $item->id)),
                        delete_column(route('postCategory.delete', $item->id)),
                    ]),
                ],
                rawColumns: ['status', 'actions']
            );
        }

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('postCategory', 'list'));
    }

    public function create(Request $request)
    {
        $data['pageTitle'] = __('Create Post Category');
        $data['function_type'] = 'create';
        $data['parents'] = $this->service->postCategoryCreateData($request)['data']['parents'];

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('postCategory', 'create'));
    }

    public function store(PostCategoryCreateRequest $request): RedirectResponse
    {
        $response = $this->service->storeOrUpdatePostCategory($request);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'postCategory.list');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $response = $this->service->postCategoryEditData($id);
        if ($response['success'] === false) {
            return ResponseService::send();
        }

        $request = request();
        $request->merge(['id' => $id]);

        $data['pageTitle'] = __('Update Post Category');
        $data['function_type'] = 'update';
        $data['item'] = $response['data'];
        $data['parents'] = $this->service->postCategoryCreateData($request)['data']['parents'];

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('postCategory', 'create'));
    }

    public function update(PostCategoryCreateRequest $request, string $id): RedirectResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->service->storeOrUpdatePostCategory($request);

        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'postCategory.list');
    }

    public function destroy(string $id): RedirectResponse
    {
        $response = $this->service->deletePostCategory($id);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'postCategory.list');
    }

    public function postCategoryStatus(Request $request): JsonResponse
    {
        try {
            $response = $this->service->publishPostCategory($request->id, $request->status);
            return response()->json($response);
        } catch (\Exception $e) {
            logStore('postCategoryStatus', $e->getMessage());
            return response()->json(['success' => false, 'message' => somethingWrong()]);
        }
    }
}
