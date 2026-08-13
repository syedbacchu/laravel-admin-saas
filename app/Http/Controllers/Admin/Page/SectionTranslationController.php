<?php

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\Controller;
use App\Http\Services\SectionTranslation\SectionTranslationServiceInterface;
use App\Http\Services\Response\ResponseService;
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
        $response = $this->translationService->getTranslationListData((int)$pageId, (int)$sectionId);
        if (!$response) {
            return ResponseService::send();
        }

        return view(viewss('section-translation', 'list'), $response['data']);
    }

    public function destroy(string $pageId, string $sectionId, string $id): RedirectResponse
    {
        $response = $this->translationService->deleteTranslation((int)$id);
        return ResponseService::send([
            'response' => $response,
        ], null, null, ['pageId' => $pageId, 'sectionId' => $sectionId], 'pages.sections.translations.index');
    }

    public function updateContent(Request $request, string $pageId, string $sectionId): RedirectResponse
    {
        $languageId = $request->input('language_id');
        $contentData = $request->input('data', []);

        // Clean up new items in repeaters
        $contentData = $this->cleanRepeaterData($contentData);

        $response = $this->translationService->saveTranslationContent((int)$sectionId, (int)$languageId, $contentData);

        return ResponseService::send([
            'response' => $response,
        ], null, null, ['pageId' => $pageId, 'sectionId' => $sectionId, 'language_id' => $languageId], 'pages.sections.translations.tabbed');
    }

    private function cleanRepeaterData(array $data): array
    {
        $cleaned = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Check if this array contains 'new_' keys (new repeater items)
                $hasNewItems = false;
                $regularItems = [];
                $newItems = [];

                foreach ($value as $itemKey => $itemValue) {
                    if (str_starts_with($itemKey, 'new_')) {
                        $hasNewItems = true;
                        // Check if this new item has any non-empty values
                        if (is_array($itemValue) && $this->hasNonEmptyValues($itemValue)) {
                            $newItems[] = $itemValue;
                        }
                    } else {
                        $regularItems[$itemKey] = $itemValue;
                    }
                }

                if ($hasNewItems) {
                    // Combine regular items with valid new items
                    $cleaned[$key] = array_merge($regularItems, $newItems);
                } else {
                    // Process nested arrays recursively
                    $cleaned[$key] = $this->cleanRepeaterData($value);
                }
            } else {
                $cleaned[$key] = $value;
            }
        }

        return $cleaned;
    }

    private function hasNonEmptyValues(array $item): bool
    {
        foreach ($item as $value) {
            if (!empty($value) && $value !== '') {
                return true;
            }
        }
        return false;
    }

    public function getDefaultLanguageContent(Request $request, string $pageId, string $sectionId): JsonResponse
    {
        try {
            $defaultLanguage = \App\Models\Language::where('is_default', 1)->first();
            if (!$defaultLanguage) {
                return response()->json(['success' => false, 'message' => __('Default language not found')], 404);
            }

            $defaultTranslation = $this->translationService->getTranslationBySectionAndLanguage((int)$sectionId, (int)$defaultLanguage->id);
            if (!$defaultTranslation) {
                return response()->json(['success' => false, 'message' => __('Default language translation not found')], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'language' => [
                        'id' => $defaultLanguage->id,
                        'name' => $defaultLanguage->name,
                        'code' => $defaultLanguage->code,
                    ],
                    'content' => $defaultTranslation->data ?? [],
                ]
            ]);
        } catch (\Exception $e) {
            logStore('getDefaultLanguageContent', $e->getMessage());
            return response()->json(['success' => false, 'message' => __('Something went wrong')], 500);
        }
    }

    public function tabbedEdit(Request $request, string $pageId, string $sectionId)
    {
        $response = $this->translationService->getTabbedEditData((int)$pageId, (int)$sectionId);
        if (!$response) {
            return ResponseService::send();
        }

        return view(viewss('section-translation', 'tabbed-edit'), $response['data']);
    }
}
