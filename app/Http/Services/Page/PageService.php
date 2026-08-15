<?php

namespace App\Http\Services\Page;

use App\Enums\StatusEnum;
use App\Http\Services\BaseService;
use App\Models\Language;
use App\Models\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageService extends BaseService implements PageServiceInterface
{
    protected PageRepositoryInterface $pageRepository;

    public function __construct(PageRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->pageRepository = $repository;
    }

    public function getDataTableData(Request $request): array
    {
        $data = $this->pageRepository->pageList($request);
        return $this->sendResponse(true, __('Data retrieved successfully'), $data);
    }

    public function storeOrUpdatePage(Request $request): array
    {
        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->slug ?? $request->name),
            'heading' => $request->heading ?? null,
            'sub_heading' => $request->sub_heading ?? null,
            'short_description' => $request->short_description ?? null,
            'full_description' => $request->full_description ?? null,
            'banner' => $request->banner ?? null,
            'meta_title' => $request->meta_title ?? null,
            'meta_description' => $request->meta_description ?? null,
            'meta_image' => $request->meta_image ?? null,
            'status' => $request->status ?? true,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ];

        if ($request->edit_id) {
            $existingPage = $this->pageRepository->find($request->edit_id);
            if (!$existingPage) {
                return $this->sendResponse(false, __('Page not found'));
            }

            // Ensure slug uniqueness
            $data['slug'] = $existingPage->slug === $data['slug']
                ? $data['slug']
                : Str::slug($data['slug']);

            $this->pageRepository->update($existingPage->id, $data);
            $page = $this->pageRepository->find($existingPage->id);
            return $this->sendResponse(true, __('Page updated successfully'), $page);
        } else {
            $page = $this->pageRepository->create($data);
            return $this->sendResponse(true, __('Page created successfully'), $page);
        }
    }

    public function deletePage(int $id): array
    {
        $page = $this->pageRepository->find($id);
        if (!$page) {
            return $this->sendResponse(false, __('Page not found'));
        }

        // Check if page has sections
        if ($page->sections()->count() > 0) {
            return $this->sendResponse(false, __('Cannot delete page that has sections. Delete sections first.'));
        }

        $this->pageRepository->delete($id);
        return $this->sendResponse(true, __('Page deleted successfully'));
    }

    public function togglePageStatus(int $id, bool $status): array
    {
        $page = $this->pageRepository->find($id);
        if (!$page) {
            return $this->sendResponse(false, __('Page not found'));
        }

        $this->pageRepository->update($id, ['status' => $status]);
        return $this->sendResponse(true, __('Page status updated successfully'));
    }

    public function getPageWithSections(int $id): ?Model
    {
        return $this->pageRepository->find($id);
    }

    public function getActivePages(): Collection
    {
        return $this->pageRepository->getActivePages();
    }

    public function findBySlug(string $slug): ?Model
    {
        return $this->pageRepository->findBySlug($slug);
    }

    public function getPagesWithComponents(): Collection
    {
        return $this->pageRepository->getPagesWithComponents();
    }

    public function getPageContent(string $slug, string $languageCode = null): array
    {
        $page = $this->findBySlug($slug);
        if (!$page) {
            return [];
        }

        // Load page with all sections, translations, and components
        $page->load(['sections.component', 'sections.translations.language']);

        if (!$page->sections || $page->sections->isEmpty()) {
            return [];
        }

        $languageCode = $languageCode ?: 'en';

        $content = [];

        foreach ($page->sections as $section) {
            if (!$section->component) {
                continue;
            }

            // Find the translation for the requested language
            $translation = $section->translations
                ->firstWhere('language.code', $languageCode);

            if (!$translation) {
                // Fallback to English if available
                $translation = $section->translations
                    ->firstWhere('language.code', 'en');
            }

            if ($translation && $translation->data) {
                $content[] = [
                    'section_id' => $section->id,
                    'component' => [
                        'id' => $section->component->id,
                        'name' => $section->component->name,
                        'slug' => $section->component->slug,
                    ],
                    'sort_order' => $section->sort_order,
                    'is_visible' => $section->is_visible,
                    'content' => $translation->data,
                ];
            }
        }

        // Sort by sort_order
        usort($content, function($a, $b) {
            return $a['sort_order'] <=> $b['sort_order'];
        });

        return [
            'page' => [
                'id' => $page->id,
                'name' => $page->name,
                'slug' => $page->slug,
                'meta_title' => $page->meta_title,
                'meta_description' => $page->meta_description,
                'meta_image' => $page->meta_image,
                'status' => $page->status,
            ],
            'sections' => $content,
        ];
    }

    public function getPublicPageBySlug(string $slug, ?string $languageCode = null): array
    {
        try {
            // Get language from code or use default
            $language = null;

            if ($languageCode) {
                $language = Language::where('code', $languageCode)
                    ->where('status', 1)
                    ->first();
            }

            // If no specific language requested or not found, use default
            if (!$language) {
                $language = Language::where('is_default', 1)
                    ->where('status', 1)
                    ->first();
            }

            // If still no language, return error
            if (!$language) {
                return $this->sendResponse(
                    false,
                    'No active language found',
                    [],
                    404,
                    'Language configuration error'
                );
            }

            // Find page with sections
            $page = $this->pageRepository->findPageWithSectionsBySlug($slug, $language->id);

            // If page not found
            if (!$page) {
                return $this->sendResponse(
                    false,
                    'Page not found',
                    [],
                    404,
                    'The requested page does not exist or is not active'
                );
            }

            // Format the response data
            $formattedData = $this->formatPageData($page, $language);

            return $this->sendResponse(
                true,
                'Page data retrieved successfully',
                $formattedData,
                200,
                ''
            );

        } catch (\Exception $e) {
            return $this->sendResponse(
                false,
                'Something went wrong',
                [],
                500,
                $e->getMessage()
            );
        }
    }

    /**
     * Format page data for API response
     *
     * @param \App\Models\Page $page
     * @param \App\Models\Language $language
     * @return array
     */
    private function formatPageData($page, $language): array
    {
        $sections = [];

        foreach ($page->activeSections as $section) {
            $sectionData = [
                'id' => $section->id,
                'component_id' => $section->component_id,
                'component_name' => $section->component->name ?? null,
                'component_identifier' => $section->component->identifier ?? null,
                'sort_order' => $section->sort_order,
                'is_visible' => $section->is_visible,
                'content' => []
            ];

            // Get translation data if exists
            $translation = $section->translations->first();
            if ($translation && !empty($translation->data)) {
                $sectionData['content'] = $translation->data;
            }

            $sections[] = $sectionData;
        }

        return [
            'page' => [
                'id' => $page->id,
                'name' => $page->name,
                'slug' => $page->slug,
                'meta_title' => $page->meta_title,
                'meta_description' => $page->meta_description,
                'meta_keywords' => $page->meta_keyword,
                'meta_image' => $page->meta_image,
                'status' => $page->status,
            ],
            'language' => $language->code,
            'sections' => $sections
        ];
    }

    public function getPublicPagesList(Request $request): array
    {
        $request->merge(['status' => enum(StatusEnum::ACTIVE)]);
        $pages = $this->pageRepository->pageList($request);

        return $this->sendResponse(
            true,
            'Pages list retrieved successfully',
            $pages,
            200,
            ''
        );
    }
}
