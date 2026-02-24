<?php

namespace App\Http\Controllers\Admin\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\PostCreateRequest;
use App\Http\Services\Post\PostServiceInterface;
use App\Http\Services\Response\ResponseService;
use App\Support\DataListManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    protected PostServiceInterface $service;

    public function __construct(PostServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __('Post List');
        if ($request->ajax()) {
            return DataListManager::dataTableHandle(
                request: $request,
                dataProvider: function ($request) {
                    return $this->service
                        ->getDataTableData($request)['data']['data'];
                },
                columns: [
                    'categories' => fn ($item) => $item->categories->pluck('name')->implode(', ') ?: '-',
                    'tags' => fn ($item) => $item->tags->pluck('name')->implode(', ') ?: '-',

                    'status_toggle' => fn ($item) =>
                    toggle_column(
                        route('post.publish'),
                        $item->id,
                        $item->status === 'published'
                    ),

                    'actions' => function ($item) {
                        $buttons = [
                            edit_column(route('post.edit', $item->id)),
                            delete_column(route('post.delete', $item->id)),
                        ];

                        if (canAccess('postComment.list')) {
                            $buttons[] = '<a href="'.route('postComment.list', ['post_id' => $item->id]).'" class="inline-flex items-center px-3 py-1.5 text-sm font-semibold text-indigo-600 hover:text-white hover:bg-indigo-600 border border-indigo-600 rounded-lg transition duration-200">Comments</a>';
                        }

                        return action_buttons($buttons);
                    },
                ],
                rawColumns: ['status_toggle', 'actions']
            );
        }

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('post', 'list'));
    }

    public function create(Request $request)
    {
        $setup = $this->service->postCreateData($request)['data'];

        $data['pageTitle'] = __('Create Post');
        $data['function_type'] = 'create';
        $data['categories'] = $setup['categories'];
        $data['tags'] = $setup['tags'];

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('post', 'create'));
    }

    public function store(PostCreateRequest $request): RedirectResponse
    {
        $response = $this->service->storeOrUpdatePost($request);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'post.list');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $response = $this->service->postEditData($id);
        if ($response['success'] === false) {
            return ResponseService::send();
        }

        $setup = $this->service->postCreateData(request())['data'];
        $item = $response['data'];

        $data['pageTitle'] = __('Update Post');
        $data['function_type'] = 'update';
        $data['item'] = $item;
        $data['categories'] = $setup['categories'];
        $data['tags'] = $setup['tags'];
        $data['selectedCategoryIds'] = $item->categories->pluck('id')->toArray();
        $data['selectedTagIds'] = $item->tags->pluck('id')->toArray();

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('post', 'create'));
    }

    public function update(PostCreateRequest $request, string $id): RedirectResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->service->storeOrUpdatePost($request);

        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'post.list');
    }

    public function destroy(string $id): RedirectResponse
    {
        $response = $this->service->deletePost($id);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'post.list');
    }

    public function postStatus(Request $request): JsonResponse
    {
        try {
            $response = $this->service->publishPost($request->id, $request->status);
            return response()->json($response);
        } catch (\Exception $e) {
            logStore('postStatus', $e->getMessage());
            return response()->json(['success' => false, 'message' => somethingWrong()]);
        }
    }
}
