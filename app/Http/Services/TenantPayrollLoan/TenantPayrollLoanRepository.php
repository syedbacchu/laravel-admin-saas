<?php

namespace App\Http\Services\TenantPayrollLoan;

use App\Http\Repositories\BaseRepository;
use App\Models\TenantPayrollLoan;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TenantPayrollLoanRepository extends BaseRepository implements TenantPayrollLoanRepositoryInterface
{
    public function __construct(TenantPayrollLoan $model)
    {
        parent::__construct($model);
    }

    public function loanList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: TenantPayrollLoan::query(),
            searchable: [
                'status',
            ],
            filters: [
                'employee_id' => [
                    'column' => 'employee_id',
                ],
                'status' => [
                    'column' => 'status',
                ],
                'loan_date' => [
                    'column' => 'loan_date',
                    'type' => 'date',
                ],
            ],
            select: [
                'id',
                'added_by',
                'updated_by',
                'loan_date',
                'employee_id',
                'loan_amount',
                'monthly_deduction',
                'after_adjustment_amount',
                'remaining_balance',
                'paid_amount',
                'status',
                'created_at',
                'updated_at',
            ],
        );
    }

    public function createLoan(array $data): Model
    {
        return $this->create($data);
    }

    public function findLoan(int $id): ?TenantPayrollLoan
    {
        return TenantPayrollLoan::query()->find($id);
    }
}
