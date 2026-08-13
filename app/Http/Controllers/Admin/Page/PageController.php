<?php

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\Controller;
use App\Http\Services\Page\PageServiceInterface;
use App\Http\Services\Response\ResponseService;
use App\Support\DataListManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    protected PageServiceInterface $pageService;

    public function __construct(PageServiceInterface $pageService)
    {
        $this->pageService = $pageService;
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __('Page List');

        if ($request->ajax()) {
            return DataListManager::dataTableHandle(
                request: $request,
                dataProvider: function ($request) {
                    return $this->pageService
                        ->getDataTableData($request)['data']['data'];
                },
                columns: [
                    'name' => fn ($item) => $item->name,

                    'slug' => fn ($item) => '<span class="font-mono text-sm">' . $item->slug . '</span>',

                    'status_toggle' => fn ($item) =>
                    toggle_column(
                        route('pages.toggleStatus'),
                        $item->id,
                        $item->status == 1
                    ),

                    'sections_count' => fn ($item) => '<span class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-sm font-medium">' . ($item->sections_count ?? $item->sections->count()) . ' ' . __('sections') . '</span>',

                    'created_at' => fn ($item) => ($item->created_at),

                    'actions' => function ($item) {
                        $buttons = [
                            section_button(route('pages.sections.index', ['pageId' => $item->id])),
                            edit_column(route('pages.edit', $item->id)),
                            delete_column(route('pages.delete', $item->id)),
                        ];

                        return action_buttons($buttons);
                    },
                ],
                rawColumns: ['name', 'slug', 'status_toggle', 'sections_count', 'created_at', 'actions']
            );
        }

        return view(viewss('page', 'list'), $data);
    }

    public function create(Request $request)
    {
        $data['pageTitle'] = __('Create Page');
        $data['function_type'] = 'create';

        return view(viewss('page', 'create'), $data);
    }

    public function store(Request $request): RedirectResponse
    {
        $response = $this->pageService->storeOrUpdatePage($request);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'pages.index');
    }

    public function show(string $id)
    {
        $response = $this->pageService->getPageWithSections($id);
        if (!$response) {
            return ResponseService::send();
        }

        $data['pageTitle'] = __('Page Details');
        $data['page'] = $response;
        $data['function_type'] = 'view';

        return view(viewss('page', 'show'), $data);
    }

    public function edit(string $id)
    {
        $response = $this->pageService->getPageWithSections($id);
        if (!$response) {
            return ResponseService::send();
        }

        $page = $response;
        $data['pageTitle'] = __('Update Page');
        $data['function_type'] = 'update';
        $data['page'] = $page;

        return view(viewss('page', 'edit'), $data);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->pageService->storeOrUpdatePage($request);

        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'pages.index');
    }

    public function destroy(string $id): RedirectResponse
    {
        $response = $this->pageService->deletePage($id);
        return ResponseService::send([
            'response' => $response,
        ], successRoute: 'pages.index');
    }

    public function toggleStatus(Request $request): JsonResponse
    {
        try {
            $response = $this->pageService->togglePageStatus($request->id, $request->status);
            return response()->json($response);
        } catch (\Exception $e) {
            logStore('pageToggleStatus', $e->getMessage());
            return response()->json(['success' => false, 'message' => somethingWrong()]);
        }
    }

    public function sections(Request $request, string $pageId)
    {
        $pageResponse = $this->pageService->getPageWithSections($pageId);
        if (!$pageResponse) {
            return ResponseService::send();
        }

        $page = $pageResponse;
        $pageTitle = __('Manage Sections: ') . $page->name;

        if ($request->ajax()) {
            return DataListManager::dataTableHandle(
                request: $request,
                dataProvider: function ($request) use ($pageId) {
                    $request->merge(['page_id' => $pageId]);
                    return app(\App\Http\Services\PageSection\PageSectionServiceInterface::class)
                        ->getDataTableData($request, $pageId)['data']['data'];
                },
                columns: [

                    'component' => fn ($item) => '<span class="font-semibold">' . $item->component->name . '</span>',

                    'sort_order' => fn ($item) => '<span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-medium">' . $item->sort_order . '</span>',

                    'visibility_toggle' => fn ($item) =>
                    toggle_column(
                        route('pages.sections.toggleStatus', ['pageId' => $pageId, 'sectionId' => $item->id]),
                        $item->id,
                        $item->is_visible == 1
                    ),

                    'translations_count' => fn ($item) => '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">' . ($item->translations_count ?? $item->translations->count()) . ' ' . __('translations') . '</span>',

                    'created_at' => fn ($item) => ($item->created_at),

                    'actions' => function ($item) use ($pageId) {
                        $buttons = [
                            translation_button(route('pages.sections.translations.tabbed', ['pageId' => $pageId, 'sectionId' => $item->id])),
                            edit_column(route('pages.sections.edit', ['pageId' => $pageId, 'id' => $item->id])),
                            delete_column(route('pages.sections.delete', ['pageId' => $pageId, 'sectionId' => $item->id])),
                        ];

                        return action_buttons($buttons);
                    },
                ],
                rawColumns: ['id', 'component', 'sort_order', 'visibility_toggle', 'translations_count', 'created_at', 'actions']
            );
        }

        return view(viewss('page', 'sections'), compact('pageTitle', 'page'));
    }
}
