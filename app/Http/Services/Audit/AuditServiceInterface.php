<?php

namespace App\Http\Services\Audit;

use App\Http\Services\BaseServiceInterface;
use Illuminate\Http\Request;

interface AuditServiceInterface extends BaseServiceInterface
{
    public function getDataTableData(Request $request): array; // For DataTable - Service provides this
    public function deleteData($id): mixed; // For delete data
    public function detailsData($id): mixed; // For delete data
}
