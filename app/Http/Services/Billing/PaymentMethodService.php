<?php

namespace App\Http\Services\Billing;

use App\Http\Requests\Billing\PaymentMethodCreateRequest;
use App\Http\Services\BaseService;
use App\Models\Language;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Throwable;

class PaymentMethodService extends BaseService implements PaymentMethodServiceInterface
{
    protected PaymentMethodRepositoryInterface $paymentMethodRepository;

    public function __construct(PaymentMethodRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->paymentMethodRepository = $repository;
    }

    public function getDataTableData($request): array
    {
        $data = $this->paymentMethodRepository->paymentMethodList($request);
        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function paymentMethodCreateData($request): array
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

    public function paymentMethodEditData($id): array
    {
        $item = $this->paymentMethodRepository->find((int) $id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        $item->load('translations');

        return $this->sendResponse(true, '', $item);
    }

    public function storeOrUpdatePaymentMethod(PaymentMethodCreateRequest $request): array
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
                $details = $this->buildDetailsPayload((array) $request->input('details', []));

                $data = [
                    'code' => $request->code,
                    'name' => $defaultName,
                    'description' => $defaultDescription !== '' ? $defaultDescription : null,
                    'details_json' => $details,
                    'sort_order' => (int) ($request->sort_order ?? 0),
                    'is_active' => (int) ($request->is_active ?? 1),
                ];

                if ($request->edit_id) {
                    $item = $this->paymentMethodRepository->find((int) $request->edit_id);
                    if (!$item) {
                        return $this->sendResponse(false, __('Data not found'));
                    }

                    $this->paymentMethodRepository->update($item->id, $data);
                    $item = PaymentMethod::query()->find((int) $item->id);
                    $message = __('Payment method updated successfully');
                } else {
                    $item = $this->paymentMethodRepository->createPaymentMethod($data);
                    $message = __('Payment method created successfully');
                }

                $this->syncTranslations(
                    $item,
                    $translations,
                    $languages->pluck('id')->map(fn ($id) => (int) $id)->all(),
                    (int) $defaultLanguage->id
                );

                return $this->sendResponse(true, $message, $item->fresh(['translations']));
            });
        } catch (Throwable $e) {
            logStore('PaymentMethodService storeOrUpdatePaymentMethod', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function deletePaymentMethod($id): array
    {
        $item = $this->paymentMethodRepository->find((int) $id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        try {
            $this->paymentMethodRepository->delete((int) $id);
            return $this->sendResponse(true, __('Data deleted successfully'));
        } catch (Throwable $e) {
            return $this->sendResponse(false, __('This payment method cannot be deleted because it is used by payments'));
        }
    }

    public function publishPaymentMethod($id, $status): array
    {
        $item = $this->paymentMethodRepository->find((int) $id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        $this->paymentMethodRepository->update((int) $id, [
            'is_active' => (int) $status === 1 ? 1 : 0,
        ]);

        return $this->sendResponse(true, __('Status updated successfully'));
    }

    protected function syncTranslations(PaymentMethod $item, array $translations, array $allowedLanguageIds, int $defaultLanguageId): void
    {
        foreach ($translations as $languageId => $row) {
            $langId = (int) $languageId;
            if (!in_array($langId, $allowedLanguageIds, true)) {
                continue;
            }

            $name = trim((string) data_get($row, 'name', ''));
            $description = trim((string) data_get($row, 'description', ''));

            if ($name === '' && $description === '' && $langId !== $defaultLanguageId) {
                $item->translations()->where('language_id', $langId)->delete();
                continue;
            }

            $item->translations()->updateOrCreate(
                ['language_id' => $langId],
                [
                    'name' => $name !== '' ? $name : $item->name,
                    'description' => $description !== '' ? $description : null,
                ]
            );
        }
    }

    protected function buildDetailsPayload(array $input): ?array
    {
        $payload = [
            'mobile_number' => trim((string) ($input['mobile_number'] ?? '')),
            'account_number' => trim((string) ($input['account_number'] ?? '')),
            'bank_name' => trim((string) ($input['bank_name'] ?? '')),
            'branch_name' => trim((string) ($input['branch_name'] ?? '')),
        ];

        foreach ($payload as $key => $value) {
            if ($value === '') {
                $payload[$key] = null;
            }
        }

        foreach ($payload as $value) {
            if ($value !== null) {
                return $payload;
            }
        }

        return null;
    }
}

