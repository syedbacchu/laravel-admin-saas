<?php

namespace App\Http\Services\TenantFile;

use App\Http\Requests\TenantApi\TenantFileUploadRequest;
use App\Http\Requests\TenantApi\TenantFileUpdateRequest;
use Illuminate\Http\Request;

interface TenantFileServiceInterface
{
    public function fileList(Request $request): array;
    public function uploadFile(TenantFileUploadRequest $request): array;
    public function updateFileMeta(TenantFileUpdateRequest $request, int $id): array;
    public function deleteFile(Request $request, int $id): array;
}
