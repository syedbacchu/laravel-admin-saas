<?php

namespace App\Http\Services\Billing;

use App\Http\Requests\Billing\FeatureCreateRequest;
use App\Http\Services\BaseService;
use App\Models\Feature;
use App\Models\Language;
use Illuminate\Support\Facades\DB;
use Throwable;

class FeatureService extends BaseService implements FeatureServiceInterface
{
    protected FeatureRepositoryInterface $featureRepository;

    public function __construct(FeatureRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->featureRepository = $repository;
    }

    public function getDataTableData($request): array
    {
        $data = $this->featureRepository->featureList($request);
        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function featureCreateData($request): array
    {
        $languages = Language::query()
            ->forInput()
            ->get(['id', 'name', 'native_name', 'code', 'direction', 'is_default']);

        $defaultLanguage = $languages->firstWhere('is_default', 1);

        return $this->sendResponse(true, '', [
            'languages' => $languages,
            'default_language' => $defaultLanguage,
        ]);
    }

    public function storeOrUpdateFeature(FeatureCreateRequest $request): array
    {
        try {
            return DB::transaction(function () use ($request) {
                $languages = Language::query()
                    ->forInput()
                    ->get(['id', 'is_default']);

                $defaultLanguage = $languages->firstWhere('is_default', 1);
                if (!$defaultLanguage) {
                    return $this->sendResponse(false, __('Default language is missing'));
                }

                $translations = (array) $request->input('translations', []);
                $defaultInput = (array) ($translations[$defaultLanguage->id] ?? []);
                $defaultName = trim((string) ($defaultInput['name'] ?? ''));
                $defaultDescription = trim((string) ($defaultInput['description'] ?? ''));

                $data = [
                    'key' => $request->key,
                    'name' => $defaultName,
                    'description' => $defaultDescription !== '' ? $defaultDescription : null,
                    'value_type' => $request->value_type,
                    'is_active' => (int) ($request->is_active ?? 1),
                ];

                if ($request->edit_id) {
                    $item = $this->featureRepository->find((int) $request->edit_id);
                    if (!$item) {
                        return $this->sendResponse(false, __('Data not found'));
                    }

                    $this->featureRepository->update($item->id, $data);
                    $item = Feature::query()->find((int) $item->id);
                    $message = __('Feature updated successfully');
                } else {
                    $item = $this->featureRepository->createFeature($data);
                    $message = __('Feature created successfully');
                }

                $this->syncTranslations($item, $translations, $languages->pluck('id')->map(fn ($id) => (int) $id)->all(), (int) $defaultLanguage->id);

                return $this->sendResponse(true, $message, $item->fresh(['translations']));
            });
        } catch (Throwable $e) {
            logStore('FeatureService storeOrUpdateFeature', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function deleteFeature($id): array
    {
        $item = $this->featureRepository->find((int) $id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        $this->featureRepository->delete((int) $id);
        return $this->sendResponse(true, __('Data deleted successfully'));
    }

    public function featureEditData($id): array
    {
        $item = $this->featureRepository->find((int) $id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        $item->load('translations');

        return $this->sendResponse(true, '', $item);
    }

    protected function syncTranslations(Feature $feature, array $translations, array $allowedLanguageIds, int $defaultLanguageId): void
    {
        foreach ($translations as $languageId => $row) {
            $langId = (int) $languageId;
            if (!in_array($langId, $allowedLanguageIds, true)) {
                continue;
            }

            $name = trim((string) data_get($row, 'name', ''));
            $description = trim((string) data_get($row, 'description', ''));

            if ($name === '' && $description === '' && $langId !== $defaultLanguageId) {
                $feature->translations()
                    ->where('language_id', $langId)
                    ->delete();
                continue;
            }

            $feature->translations()->updateOrCreate(
                ['language_id' => $langId],
                [
                    'name' => $name !== '' ? $name : $feature->name,
                    'description' => $description !== '' ? $description : null,
                ]
            );
        }
    }
}
