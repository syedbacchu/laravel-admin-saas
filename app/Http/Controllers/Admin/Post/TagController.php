<?php

namespace App\Http\Controllers\Admin\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\TagCreateRequest;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\Tag\TagServiceInterface;
use App\Support\DataListManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    protected TagServiceInterface $service;

    public function __construct(TagServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __('Tag List');
        if ($request->ajax()) {
            return DataListManager::dataTableHandle(
                request: $request,
                dataProvider: function ($request) {
                    return $this->service
                        ->getDataTableData($request)['data']['data'];
                },
                columns: [
                    'actions' => fn ($item) =>
                    action_buttons([
                        edit_column(route('tag.edit', $item->id)),
                        delete_column(route('tag.delete', $item->id)),
                    ]),
                ],
                rawColumns: ['actions']
            );
        }

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('tag', 'list'));
    }

    public function create(Request $request)
    {
        $data['pageTitle'] = __('Create Tag');
        $data['function_type'] = 'create';

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('tag', 'create'));
    }

    public function store(TagCreateRequest $request): RedirectResponse
    {
        $response = $this->service->storeOrUpdateTag($request);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'tag.list');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $response = $this->service->tagEditData($id);
        if ($response['success'] === false) {
            return ResponseService::send();
        }

        $data['pageTitle'] = __('Update Tag');
        $data['function_type'] = 'update';
        $data['item'] = $response['data'];

        return ResponseService::send([
            'data' => $data,
        ], view: viewss('tag', 'create'));
    }

    public function update(TagCreateRequest $request, string $id): RedirectResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->service->storeOrUpdateTag($request);

        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'tag.list');
    }

    public function destroy(string $id): RedirectResponse
    {
        $response = $this->service->deleteTag($id);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'tag.list');
    }
}
