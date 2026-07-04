<?php

namespace App\Http\Services\TenantPayrollGenerateSalary;

use App\Http\Repositories\BaseRepository;
use App\Models\TenantPayrollGeneratedSalary;
use App\Models\TenantPayrollSalarySheet;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TenantPayrollGenerateSalaryRepository extends BaseRepository implements TenantPayrollGenerateSalaryRepositoryInterface
{
    public function __construct(TenantPayrollGeneratedSalary $model)
    {
        parent::__construct($model);
    }

    public function generatedSalaryList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: TenantPayrollGeneratedSalary::query(),
            searchable: [
                'month',
            ],
            filters: [
                'month' => [
                    'column' => 'month',
                ],
                'status' => [
                    'column' => 'status',
                ],
                'generate_date' => [
                    'column' => 'generate_date',
                    'type' => 'date',
                ],
            ],
            select: [
                'id',
                'added_by',
                'updated_by',
                'generate_date',
                'month',
                'generated_by',
                'status',
                'created_at',
                'updated_at',
            ],
        );
    }

    public function createGeneratedSalary(array $data): Model
    {
        return $this->create($data);
    }

    public function findGeneratedSalary(int $id): ?TenantPayrollGeneratedSalary
    {
        return TenantPayrollGeneratedSalary::query()->find($id);
    }

    public function deleteSalarySheetRows(int $generatedSalaryId): int
    {
        return TenantPayrollSalarySheet::query()
            ->where('generated_salary_id', $generatedSalaryId)
            ->delete();
    }

    public function insertSalarySheetRows(array $rows): bool
    {
        if (empty($rows)) {
            return true;
        }

        return TenantPayrollSalarySheet::query()->insert($rows);
    }

    public function salarySheetRows(int $generatedSalaryId): EloquentCollection
    {
        return TenantPayrollSalarySheet::query()
            ->where('generated_salary_id', $generatedSalaryId)
            ->orderBy('id')
            ->get();
    }
}
