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
}
