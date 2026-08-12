<?php

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\Controller;
use App\Http\Services\SectionTranslation\SectionTranslationServiceInterface;
use App\Http\Services\Response\ResponseService;
use App\Models\Language;
use App\Models\Page;
use App\Models\PageSection;
use App\Support\DataListManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SectionTranslationController extends Controller
{
    protected SectionTranslationServiceInterface $translationService;

    public function __construct(SectionTranslationServiceInterface $translationService)
    {
        $this->translationService = $translationService;
    }

    public function index(Request $request, string $pageId, string $sectionId)
    {
        $page = Page::findOrFail($pageId);
        $section = PageSection::with('component')->findOrFail($sectionId);

        $pageTitle = __('Translations: ') . $section->component->title;
        $translations = $this->translationService->getTranslationsBySection((int)$sectionId);

        return view(viewss('section-translation', 'list'), compact('pageTitle', 'page', 'section', 'translations'));
    }

    public function create(Request $request, string $pageId, string $sectionId)
    {
        $page = Page::findOrFail($pageId);
        $section = PageSection::with('component', 'component.fields')->findOrFail($sectionId);

        $data['pageTitle'] = __('Add Translation for: ') . $section->component->title;
        $data['function_type'] = 'create';
        $data['page'] = $page;
        $data['section'] = $section;
        $data['languages'] = Language::active()->get();

        // Get existing translations to show which languages are already translated
        $existingTranslations = $this->translationService->getTranslationsBySection((int)$sectionId);
        $data['existingLanguageIds'] = $existingTranslations->pluck('language_id')->toArray();

        return view(viewss('section-translation', 'create'), $data);
    }

    public function store(Request $request, string $pageId, string $sectionId): RedirectResponse
    {
        $request->merge(['page_section_id' => $sectionId]);
        $response = $this->translationService->storeOrUpdateTranslation($request, (int)$sectionId);
        return ResponseService::send([
            'response' => $response,
        ], null, null, ['pageId' => $pageId, 'sectionId' => $sectionId], 'pages.sections.translations.index');
    }

    public function edit(Request $request, string $pageId, string $sectionId, string $id)
    {
        $page = Page::findOrFail($pageId);
        $section = PageSection::with('component')->findOrFail($sectionId);

        $translationResponse = $this->translationService->getTranslationDetail((int)$id);
        if (!$translationResponse) {
            return ResponseService::send();
        }

        $translation = $translationResponse;

        $data['pageTitle'] = __('Update Translation');
        $data['function_type'] = 'update';
        $data['page'] = $page;
        $data['section'] = $section;
        $data['item'] = $translation;
        $data['languages'] = Language::active()->get();

        return view(viewss('section-translation', 'create'), $data);
    }

    public function update(Request $request, string $pageId, string $sectionId, string $id): RedirectResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->translationService->storeOrUpdateTranslation($request, (int)$sectionId);

        return ResponseService::send([
            'response' => $response,
        ], null, null, ['pageId' => $pageId, 'sectionId' => $sectionId], 'pages.sections.translations.index');
    }

    public function destroy(string $pageId, string $sectionId, string $id): RedirectResponse
    {
        $response = $this->translationService->deleteTranslation((int)$id);
        return ResponseService::send([
            'response' => $response,
        ], null, null, ['pageId' => $pageId, 'sectionId' => $sectionId], 'pages.sections.translations.index');
    }

    public function editContent(Request $request, string $pageId, string $sectionId)
    {
        $page = Page::findOrFail($pageId);
        $section = PageSection::with('component', 'component.fields')->findOrFail($sectionId);
        $languageId = $request->input('language_id', default_language_id());
        $language = Language::findOrFail($languageId);

        $translation = $this->translationService->getTranslationBySectionAndLanguage((int)$sectionId, (int)$languageId);
        $contentData = $translation ? $translation->data : [];

        $pageTitle = __('Edit Content: ') . $section->component->title . ' (' . $language->name . ')';

        return view(viewss('section-translation', 'edit-content'), compact('pageTitle', 'page', 'section', 'language', 'contentData'));
    }

    public function updateContent(Request $request, string $pageId, string $sectionId): RedirectResponse
    {
        $languageId = $request->input('language_id');
        $contentData = $request->input('data', []);

        $response = $this->translationService->saveTranslationContent((int)$sectionId, (int)$languageId, $contentData);

        return ResponseService::send([
            'response' => $response,
        ], null, null, ['pageId' => $pageId, 'sectionId' => $sectionId, 'language_id' => $languageId], 'pages.sections.translations.edit-content');
    }
}
