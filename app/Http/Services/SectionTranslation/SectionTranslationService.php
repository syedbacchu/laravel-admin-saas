<?php

namespace App\Http\Services\SectionTranslation;

use App\Http\Services\BaseService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SectionTranslationService extends BaseService implements SectionTranslationServiceInterface
{
    public function __construct(SectionTranslationRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getTranslationsBySection(int $sectionId): Collection
    {
        return $this->repository->getTranslationsBySection($sectionId);
    }

    public function getTranslationBySectionAndLanguage(int $sectionId, int $languageId): ?\App\Models\SectionTranslation
    {
        return $this->repository->getTranslationBySectionAndLanguage($sectionId, $languageId);
    }

    public function createTranslation(array $data): \App\Models\SectionTranslation
    {
        return $this->repository->createTranslation($data);
    }

    public function updateTranslation(int $id, array $data): bool
    {
        return $this->repository->updateTranslation($id, $data);
    }

    public function deleteTranslationsBySection(int $sectionId): bool
    {
        return $this->repository->deleteTranslationsBySection($sectionId);
    }

    public function getTranslationDetail(int $id): ?Model
    {
        return $this->repository->find($id);
    }

    public function storeOrUpdateTranslation(Request $request, int $sectionId): array
    {
        $data = [
            'page_section_id' => $sectionId,
            'language_id' => $request->language_id,
            'data' => $request->data ?? [],
        ];

        if ($request->edit_id) {
            $existingTranslation = $this->repository->find($request->edit_id);
            if (!$existingTranslation) {
                return $this->sendResponse(false, __('Translation not found'));
            }

            $this->repository->update($existingTranslation->id, $data);
            $translation = $this->repository->find($existingTranslation->id);
            return $this->sendResponse(true, __('Translation updated successfully'), $translation);
        } else {
            $translation = $this->repository->create($data);
            return $this->sendResponse(true, __('Translation created successfully'), $translation);
        }
    }

    public function deleteTranslation(int $id): array
    {
        $translation = $this->repository->find($id);
        if (!$translation) {
            return $this->sendResponse(false, __('Translation not found'));
        }

        $this->repository->delete($id);
        return $this->sendResponse(true, __('Translation deleted successfully'));
    }

    public function saveTranslationData(int $sectionId, int $languageId, array $data): array
    {
        $existingTranslation = $this->getTranslationBySectionAndLanguage($sectionId, $languageId);

        if ($existingTranslation) {
            $this->repository->update($existingTranslation->id, ['data' => $data]);
            $translation = $this->repository->find($existingTranslation->id);
            return $this->sendResponse(true, __('Translation updated successfully'), $translation);
        } else {
            $translation = $this->repository->create([
                'page_section_id' => $sectionId,
                'language_id' => $languageId,
                'data' => $data
            ]);
            return $this->sendResponse(true, __('Translation created successfully'), $translation);
        }
    }

    public function saveTranslationContent(int $sectionId, int $languageId, array $data): array
    {
        return $this->saveTranslationData($sectionId, $languageId, $data);
    }

    public function getTranslationListData(int $pageId, int $sectionId): ?array
    {
        $page = \App\Models\Page::with('sections')->findOrFail($pageId);
        $section = \App\Models\PageSection::with('component')->findOrFail($sectionId);

        $pageTitle = __('Translations: ') . $section->component->title;
        $translations = $this->getTranslationsBySection($sectionId);

        return $this->sendResponse(true, '', [
            'pageTitle' => $pageTitle,
            'page' => $page,
            'section' => $section,
            'translations' => $translations,
        ]);
    }

    public function getCreateTranslationData(int $pageId, int $sectionId): ?array
    {
        $page = \App\Models\Page::findOrFail($pageId);
        $section = \App\Models\PageSection::with('component', 'component.fields')->findOrFail($sectionId);

        $data = [
            'pageTitle' => __('Add Translation for: ') . $section->component->title,
            'function_type' => 'create',
            'page' => $page,
            'section' => $section,
            'languages' => \App\Models\Language::active()->get(),
        ];

        // Get existing translations to show which languages are already translated
        $existingTranslations = $this->getTranslationsBySection($sectionId);
        $data['existingLanguageIds'] = $existingTranslations->pluck('language_id')->toArray();

        // Get default language translation for copying functionality
        $defaultLanguage = \App\Models\Language::where('is_default', 1)->first();
        if ($defaultLanguage) {
            $defaultTranslation = $this->getTranslationBySectionAndLanguage($sectionId, $defaultLanguage->id);
            $data['defaultLanguage'] = $defaultLanguage;
            $data['defaultTranslation'] = $defaultTranslation;
            $data['defaultLanguageData'] = $defaultTranslation ? $defaultTranslation->data : [];
        }

        return $this->sendResponse(true, '', $data);
    }

    public function getEditTranslationData(int $pageId, int $sectionId, int $translationId): ?array
    {
        $page = \App\Models\Page::findOrFail($pageId);
        $section = \App\Models\PageSection::with('component')->findOrFail($sectionId);

        $translation = $this->getTranslationDetail($translationId);
        if (!$translation) {
            return null;
        }

        $data = [
            'pageTitle' => __('Update Translation'),
            'function_type' => 'update',
            'page' => $page,
            'section' => $section,
            'item' => $translation,
            'languages' => \App\Models\Language::active()->get(),
        ];

        return $this->sendResponse(true, '', $data);
    }

    public function getEditContentData(int $pageId, int $sectionId, int $languageId): ?array
    {
        $page = \App\Models\Page::findOrFail($pageId);
        $section = \App\Models\PageSection::with('component', 'component.fields')->findOrFail($sectionId);
        $language = \App\Models\Language::findOrFail($languageId);

        $translation = $this->getTranslationBySectionAndLanguage($sectionId, $languageId);
        $contentData = $translation ? $translation->data : [];

        $pageTitle = __('Edit Content: ') . $section->component->title . ' (' . $language->name . ')';

        return $this->sendResponse(true, '', [
            'pageTitle' => $pageTitle,
            'page' => $page,
            'section' => $section,
            'language' => $language,
            'contentData' => $contentData,
        ]);
    }

    public function getTabbedEditData(int $pageId, int $sectionId): ?array
    {
        $page = \App\Models\Page::findOrFail($pageId);
        $section = \App\Models\PageSection::with('component', 'component.fields.children')->findOrFail($sectionId);
        $languages = \App\Models\Language::active()->get();
        $defaultLanguage = $languages->firstWhere('is_default', 1);

        // Get all translations for this section
        $allTranslations = $this->getTranslationsBySection($sectionId);
        $translationsMap = $allTranslations->keyBy('language_id');

        // Prepare formatted data for each language
        $languagesData = [];
        foreach ($languages as $language) {
            $translation = $translationsMap->get($language->id);
            $contentData = $translation ? $translation->data : [];

            $languagesData[] = [
                'language' => $language,
                'translation' => $translation,
                'hasTranslation' => !is_null($translation),
                'fields' => $this->prepareFieldsData($section->component->fields, $contentData),
            ];
        }

        $pageTitle = __('Manage Translations: ') . $section->component->title;

        return $this->sendResponse(true, '', [
            'pageTitle' => $pageTitle,
            'page' => $page,
            'section' => $section,
            'languages' => $languages,
            'defaultLanguage' => $defaultLanguage,
            'languagesData' => $languagesData,
        ]);
    }

    /**
     * Prepare fields data with values - all formatting done here
     */
    private function prepareFieldsData($fields, $contentData, $parentName = ''): array
    {
        $preparedFields = [];

        foreach ($fields->where('parent_id', null)->sortBy('sort_order') as $field) {
            $fieldData = [
                'id' => $field->id,
                'name' => $field->name,
                'label' => $field->label,
                'field_type' => $field->field_type,
                'is_required' => $field->is_required,
                'config' => $field->config,
                'input_name' => $this->buildInputName($field->name, $parentName),
                'value' => $contentData[$field->name] ?? null,
                'children' => [],
            ];

            // Handle different field types
            if (in_array($field->field_type, ['repeater', 'repeatable'])) {
                // Handle repeater items
                $items = is_array($fieldData['value']) ? $fieldData['value'] : [];
                $fieldData['items'] = $this->prepareRepeaterItems($field->children, $items, $fieldData['input_name']);
                $fieldData['children'] = $this->prepareChildrenFields($field->children);
            } elseif ($field->children->isNotEmpty()) {
                // Handle nested children (for non-repeater fields)
                $childData = is_array($fieldData['value']) ? $fieldData['value'] : [];
                $fieldData['children'] = $this->prepareFieldsData($field->children, $childData, $fieldData['input_name']);
            }

            $preparedFields[] = $fieldData;
        }

        return $preparedFields;
    }

    /**
     * Prepare repeater items with child field values
     */
    private function prepareRepeaterItems($childFields, $items, $parentInputName): array
    {
        $preparedItems = [];

        foreach ($items as $index => $itemData) {
            $item = [
                'index' => $index,
                'input_name' => $parentInputName . '[' . $index . ']',
                'fields' => [],
            ];

            foreach ($childFields as $childField) {
                $childValue = $itemData[$childField->name] ?? '';

                $fieldData = [
                    'id' => $childField->id,
                    'name' => $childField->name,
                    'label' => $childField->label,
                    'field_type' => $childField->field_type,
                    'is_required' => $childField->is_required,
                    'config' => $childField->config,
                    'input_name' => $parentInputName . '[' . $index . '][' . $childField->name . ']',
                    'value' => $childValue,
                ];

                // Handle responsive_image fields
                if ($childField->field_type === 'responsive_image') {
                    $imageData = is_array($childValue) ? $childValue : ['mobile' => '', 'desktop' => ''];
                    $fieldData['mobile_value'] = $imageData['mobile'] ?? '';
                    $fieldData['desktop_value'] = $imageData['desktop'] ?? '';
                    $fieldData['mobile_input_name'] = $parentInputName . '[' . $index . '][' . $childField->name . '][mobile]';
                    $fieldData['desktop_input_name'] = $parentInputName . '[' . $index . '][' . $childField->name . '][desktop]';
                }

                $item['fields'][] = $fieldData;
            }

            $preparedItems[] = $item;
        }

        return $preparedItems;
    }

    /**
     * Prepare children fields for reference
     */
    private function prepareChildrenFields($children): array
    {
        $preparedChildren = [];

        foreach ($children->sortBy('sort_order') as $child) {
            $preparedChildren[] = [
                'id' => $child->id,
                'name' => $child->name,
                'label' => $child->label,
                'field_type' => $child->field_type,
                'is_required' => $child->is_required,
                'config' => $child->config,
            ];
        }

        return $preparedChildren;
    }

    /**
     * Build input name for form fields
     */
    private function buildInputName(string $fieldName, string $parentName = ''): string
    {
        return $parentName ? $parentName . '[' . $fieldName . ']' : $fieldName;
    }
}
