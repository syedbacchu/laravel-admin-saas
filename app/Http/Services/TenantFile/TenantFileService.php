<?php

namespace App\Http\Services\TenantFile;

use App\Http\Requests\TenantApi\TenantFileUploadRequest;
use App\Http\Requests\TenantApi\TenantFileUpdateRequest;
use App\Http\Services\BaseService;
use App\Models\Tenant;
use App\Models\TenantFile;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class TenantFileService extends BaseService implements TenantFileServiceInterface
{
    protected TenantFileRepositoryInterface $tenantFileRepository;

    public function __construct(TenantFileRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->tenantFileRepository = $repository;
    }

    public function fileList(Request $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $data = $this->tenantFileRepository->fileList($request);
        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function uploadFile(TenantFileUploadRequest $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $files = $this->resolveUploadedFiles($request);
        if (empty($files)) {
            return $this->sendResponse(false, __('Image file is required'), [], 422);
        }

        try {
            $uploadedBy = (int) ($request->user()?->id ?? 0);
            $itemIds = [];

            foreach ($files as $index => $file) {
                $uploadResponse = uploadImageFileInStorage($file, $this->resolveTenantUploadPath($tenant));
                if (($uploadResponse['success'] ?? false) !== true) {
                    return $this->sendResponse(
                        false,
                        __('File upload failed'),
                        [],
                        400,
                        __('Upload failed for image #:index. :error', [
                            'index' => (int) $index + 1,
                            'error' => (string) ($uploadResponse['error_message'] ?? $uploadResponse['message'] ?? ''),
                        ])
                    );
                }

                $uploadData = (array) ($uploadResponse['data'] ?? []);
                $fileName = (string) ($uploadData['file_name'] ?? pathinfo((string) ($uploadData['path'] ?? $file->getClientOriginalName()), PATHINFO_FILENAME));
                $originalName = (string) ($uploadData['original_name'] ?? $file->getClientOriginalName());
                $dimensions = $this->resolveDimensions($uploadData);

                $meta = [
                    'filename' => $fileName,
                    'original_name' => $originalName,
                    'type' => (string) ($uploadData['file_ext_original'] ?? $file->getClientMimeType() ?? ''),
                    'extension' => (string) ($uploadData['file_ext'] ?? $file->getClientOriginalExtension() ?? ''),
                    'size' => (int) ($uploadData['size'] ?? $file->getSize() ?? 0),
                    'path' => (string) ($uploadData['path'] ?? ''),
                    'full_url' => (string) ($uploadData['file_url'] ?? ''),
                    'dimensions' => $dimensions,
                    'alt_text' => Str::limit($fileName, 250, ''),
                    'title' => Str::limit($fileName, 250, ''),
                    'description' => $fileName,
                    'seo_keywords' => $fileName,
                    'seo_title' => Str::limit($fileName, 250, ''),
                    'seo_description' => $fileName,
                    'uploaded_by' => $uploadedBy > 0 ? $uploadedBy : null,
                ];

                $item = $this->tenantFileRepository->createTenantFile($meta);
                $itemIds[] = (int) $item->id;
            }

            if (empty($itemIds)) {
                return $this->sendResponse(false, __('Uploaded file not found'), [], 404);
            }

            $items = TenantFile::query()
                ->whereIn('id', $itemIds)
                ->orderBy('id')
                ->get();

            return $this->sendResponse(true, __('File successfully uploaded.'), $items);
        } catch (Throwable $e) {
            logStore('TenantFileService uploadFile', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function updateFileMeta(TenantFileUpdateRequest $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantFileRepository->findTenantFile($id);
        if (!$item) {
            return $this->sendResponse(false, __('File not found'), [], 404);
        }

        $data = $this->extractMetaFields($request);
        if (empty($data)) {
            return $this->sendResponse(false, __('At least one metadata field is required.'), [], 422);
        }

        $this->tenantFileRepository->update((int) $item->id, $data);
        $item = $this->tenantFileRepository->findTenantFile((int) $item->id);
        if (!$item) {
            return $this->sendResponse(false, __('File not found'), [], 404);
        }

        return $this->sendResponse(true, __('File metadata updated successfully'), $item);
    }

    public function deleteFile(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantFileRepository->findTenantFile($id);
        if (!$item) {
            return $this->sendResponse(false, __('File not found'), [], 404);
        }

        $this->deletePhysicalFile($item);
        $this->tenantFileRepository->delete((int) $item->id);

        return $this->sendResponse(true, __('File deleted successfully'));
    }

    /**
     * @return array<int, UploadedFile>
     */
    protected function resolveUploadedFiles(TenantFileUploadRequest $request): array
    {
        $files = [];

        $photos = $request->file('photo', []);
        if ($photos instanceof UploadedFile) {
            $photos = [$photos];
        }

        foreach ((array) $photos as $photo) {
            if ($photo instanceof UploadedFile) {
                $files[] = $photo;
            }
        }

        return $files;
    }

    protected function resolveTenantFromRequest(Request $request): ?Tenant
    {
        $tenant = $request->attributes->get('tenant');
        return $tenant instanceof Tenant ? $tenant : null;
    }

    protected function resolveTenantUploadPath(Tenant $tenant): string
    {
        $slug = Str::slug((string) ($tenant->company_username ?? ''));
        if ($slug === '') {
            $slug = 'tenant-' . (int) $tenant->id;
        }

        return 'uploads/tenants/' . $slug;
    }

    protected function resolveDimensions(array $uploadData): ?string
    {
        $dimensions = $uploadData['dimensions'] ?? null;
        if (is_array($dimensions)) {
            $width = (int) ($dimensions['width'] ?? 0);
            $height = (int) ($dimensions['height'] ?? 0);

            if ($width > 0 && $height > 0) {
                return $width . 'x' . $height;
            }
        }

        if (is_string($dimensions) && trim($dimensions) !== '') {
            return trim($dimensions);
        }

        return null;
    }

    protected function extractMetaFields(TenantFileUpdateRequest $request): array
    {
        $fields = ['alt_text', 'title', 'description', 'seo_keywords', 'seo_title', 'seo_description'];
        $data = [];

        foreach ($fields as $field) {
            if (!$request->exists($field)) {
                continue;
            }

            $value = $request->input($field);
            if (is_string($value)) {
                $value = trim($value);
                if ($value === '') {
                    $value = null;
                }
            }

            $data[$field] = $value;
        }

        return $data;
    }

    protected function deletePhysicalFile(TenantFile $item): void
    {
        $path = trim((string) $item->path);
        if ($path !== '') {
            Storage::disk('public')->delete($path);
        }

        $urlPath = parse_url((string) $item->full_url, PHP_URL_PATH);
        if (!is_string($urlPath) || trim($urlPath) === '') {
            return;
        }

        $storagePrefix = '/storage/';
        $position = strpos($urlPath, $storagePrefix);
        if ($position === false) {
            return;
        }

        $relativePath = ltrim(substr($urlPath, $position + strlen($storagePrefix)), '/');
        if ($relativePath === '' || $relativePath === $path) {
            return;
        }

        Storage::disk('public')->delete($relativePath);
    }
}
