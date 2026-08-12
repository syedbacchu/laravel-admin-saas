<?php

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\Controller;
use App\Http\Services\PageSection\PageSectionServiceInterface;
use App\Http\Services\Response\ResponseService;
use App\Models\Component;
use App\Models\Page;
use App\Support\DataListManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PageSectionController extends Controller
{
    protected PageSectionServiceInterface $pageSectionService;

    public function __construct(PageSectionServiceInterface $pageSectionService)
    {
        $this->pageSectionService = $pageSectionService;
    }

    public function create(Request $request, string $pageId)
    {
        $pageResponse = app(\App\Http\Services\Page\PageServiceInterface::class)->getPageWithSections($pageId);
        if (!$pageResponse) {
            return ResponseService::send();
        }

        $page = $pageResponse;

        $data['pageTitle'] = __('Add Section to: ') . $page->name;
        $data['function_type'] = 'create';
        $data['page'] = $page;
        $data['components'] = Component::active()->get();

        return view(viewss('page-section', 'create'), $data);
    }

    public function store(Request $request, string $pageId): RedirectResponse
    {
        $request->merge(['page_id' => $pageId]);
        $response = $this->pageSectionService->storeOrUpdateSection($request, (int)$pageId);
        return ResponseService::send([
            'response' => $response,
        ], null, null, ['pageId' => $pageId], 'pages.sections.index');
    }

    public function edit(Request $request, string $pageId, string $id)
    {
        $pageResponse = app(\App\Http\Services\Page\PageServiceInterface::class)->getPageWithSections($pageId);
        if (!$pageResponse) {
            return ResponseService::send();
        }

        $sectionResponse = $this->pageSectionService->getSectionDetail((int)$id);
        if (!$sectionResponse) {
            return ResponseService::send();
        }

        $page = $pageResponse;
        $section = $sectionResponse;

        $data['pageTitle'] = __('Update Section');
        $data['function_type'] = 'update';
        $data['page'] = $page;
        $data['item'] = $section;
        $data['components'] = Component::orderBy('id','desc')->get();

        return view(viewss('page-section', 'create'), $data);
    }

    public function update(Request $request, string $pageId, string $id): RedirectResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->pageSectionService->storeOrUpdateSection($request, (int)$pageId);

        return ResponseService::send([
            'response' => $response,
        ], null, null, ['pageId' => $pageId], 'pages.sections.index');
    }

    public function destroy(string $pageId, string $id): RedirectResponse
    {
        $response = $this->pageSectionService->deleteSection((int)$id);
        return ResponseService::send([
            'response' => $response,
        ], null, null, ['pageId' => $pageId], 'pages.sections.index');
    }

    public function toggleStatus(Request $request): JsonResponse
    {
        try {
            $response = $this->pageSectionService->toggleSectionVisibility($request->id, $request->status);
            return response()->json($response);
        } catch (\Exception $e) {
            logStore('sectionToggleStatus', $e->getMessage());
            return response()->json(['success' => false, 'message' => somethingWrong()]);
        }
    }

    public function updateSortOrder(Request $request): JsonResponse
    {
        try {
            $response = $this->pageSectionService->updateSectionSortOrder($request);
            return response()->json($response);
        } catch (\Exception $e) {
            logStore('sectionUpdateSortOrder', $e->getMessage());
            return response()->json(['success' => false, 'message' => somethingWrong()]);
        }
    }
}
