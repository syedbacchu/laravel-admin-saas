<?php

namespace App\Http\Services\Language;

use App\Http\Requests\Language\LanguageCreateRequest;
use App\Http\Services\BaseService;
use App\Models\Language;

class LanguageService extends BaseService implements LanguageServiceInterface
{
    protected LanguageRepositoryInterface $languageRepository;

    public function __construct(LanguageRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->languageRepository = $repository;
    }

    public function getDataTableData($request): array
    {
        $data = $this->languageRepository->languageList($request);
        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function storeOrUpdateLanguage(LanguageCreateRequest $request): array
    {
        $payload = [
            'name' => trim((string) $request->name),
            'native_name' => trim((string) ($request->native_name ?? '')) ?: null,
            'code' => strtolower(trim((string) $request->code)),
            'direction' => $request->direction ?: 'ltr',
            'sort_order' => (int) ($request->sort_order ?? 0),
            'status' => (int) ($request->status ?? 0),
            'is_default' => 0,
        ];

        if ($request->edit_id) {
            $item = $this->languageRepository->find((int) $request->edit_id);
            if (!$item) {
                return $this->sendResponse(false, __('Data not found'));
            }

            if ($item->is_default || $item->code === 'en') {
                $payload['status'] = 1;
                $payload['is_default'] = 1;
                $payload['code'] = 'en';
            } elseif ($payload['code'] === 'en') {
                $payload['status'] = 1;
            }

            $this->languageRepository->update($item->id, $payload);
            return $this->sendResponse(true, __('Language updated successfully'));
        }

        if ($payload['code'] === 'en') {
            $payload['status'] = 1;
            $payload['is_default'] = 1;
        }

        $item = $this->languageRepository->createLanguage($payload);
        return $this->sendResponse(true, __('Language created successfully'), $item);
    }

    public function deleteLanguage($id): array
    {
        $item = $this->languageRepository->find((int) $id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        if ($item->is_default || $item->code === 'en') {
            return $this->sendResponse(false, __('Default language cannot be deleted'));
        }

        $this->languageRepository->delete((int) $id);
        return $this->sendResponse(true, __('Data deleted successfully'));
    }

    public function languageEditData($id): array
    {
        $item = $this->languageRepository->find((int) $id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        return $this->sendResponse(true, '', $item);
    }

    public function publishLanguage($id, $status): array
    {
        $item = $this->languageRepository->find((int) $id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        if (($item->is_default || $item->code === 'en') && (int) $status === 0) {
            return $this->sendResponse(false, __('Default language cannot be deactivated'));
        }

        $this->languageRepository->update((int) $id, [
            'status' => (int) $status === 1 ? 1 : 0,
        ]);

        return $this->sendResponse(true, __('Status updated successfully'));
    }
}

