<?php

namespace App\Http\Services\Billing;

use App\Http\Requests\Billing\SubscriptionPaymentCreateRequest;
use App\Http\Services\BaseService;
use App\Models\PaymentMethod;
use App\Models\Subscription;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class SubscriptionPaymentService extends BaseService implements SubscriptionPaymentServiceInterface
{
    protected SubscriptionPaymentRepositoryInterface $subscriptionPaymentRepository;

    public function __construct(SubscriptionPaymentRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->subscriptionPaymentRepository = $repository;
    }

    public function getDataTableData($request): array
    {
        $data = $this->subscriptionPaymentRepository->subscriptionPaymentList($request);
        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function subscriptionPaymentCreateData($request): array
    {
        $selectedSubscriptionId = (int) ($request->subscription_id ?? 0);
        $selectedMethodId = (int) ($request->payment_method_id ?? 0);

        $tenants = Tenant::query()
            ->whereIn('status', ['active', 'provisioning'])
            ->orderBy('company_name')
            ->get(['id', 'company_name', 'company_username']);

        $subscriptions = Subscription::query()
            ->with([
                'tenant:id,company_name,company_username',
                'plan:id,name',
                'pricing:id,final_amount,currency,term_months',
            ])
            ->where(function ($query) use ($selectedSubscriptionId) {
                $query->whereIn('status', ['trialing', 'active', 'past_due', 'expired']);
                if ($selectedSubscriptionId > 0) {
                    $query->orWhere('id', $selectedSubscriptionId);
                }
            })
            ->orderByDesc('id')
            ->get([
                'id',
                'tenant_id',
                'plan_id',
                'plan_pricing_id',
                'status',
                'starts_at',
                'ends_at',
            ]);

        $paymentMethods = PaymentMethod::query()
            ->where(function ($query) use ($selectedMethodId) {
                $query->where('is_active', 1);
                if ($selectedMethodId > 0) {
                    $query->orWhere('id', $selectedMethodId);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'details_json']);

        return $this->sendResponse(true, '', [
            'tenants' => $tenants,
            'subscriptions' => $subscriptions,
            'payment_methods' => $paymentMethods,
        ]);
    }

    public function subscriptionPaymentEditData($id): array
    {
        $item = $this->subscriptionPaymentRepository->find((int) $id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        return $this->sendResponse(true, '', $item);
    }

    public function storeOrUpdateSubscriptionPayment(SubscriptionPaymentCreateRequest $request): array
    {
        try {
            return DB::transaction(function () use ($request) {
                $status = $request->status ?: 'pending';
                $paidAt = $request->paid_at ? Carbon::parse($request->paid_at) : null;
                $verifiedAt = null;
                $verifiedBy = null;

                if (in_array($status, ['verified', 'rejected'], true)) {
                    $verifiedAt = now();
                    $verifiedBy = auth()->id() ?? null;
                }

                if ($status === 'verified' && !$paidAt) {
                    $paidAt = now();
                }

                $methodDetails = $this->buildMethodDetails((array) $request->input('method_details', []));

                $data = [
                    'subscription_id' => (int) $request->subscription_id,
                    'tenant_id' => (int) $request->tenant_id,
                    'payment_method_id' => (int) $request->payment_method_id,
                    'amount' => (float) $request->amount,
                    'currency' => trim((string) ($request->currency ?: 'BDT')),
                    'status' => $status,
                    'payment_reference' => trim((string) ($request->payment_reference ?? '')) ?: null,
                    'method_details' => $methodDetails,
                    'paid_at' => $paidAt,
                    'verified_at' => $verifiedAt,
                    'verified_by' => $verifiedBy,
                    'note' => trim((string) ($request->note ?? '')) ?: null,
                ];

                if ($request->edit_id) {
                    $item = $this->subscriptionPaymentRepository->find((int) $request->edit_id);
                    if (!$item) {
                        return $this->sendResponse(false, __('Data not found'));
                    }

                    $this->subscriptionPaymentRepository->update((int) $item->id, $data);
                    $item = $this->subscriptionPaymentRepository->find((int) $item->id);
                    $message = __('Subscription payment updated successfully');
                } else {
                    $item = $this->subscriptionPaymentRepository->createSubscriptionPayment($data);
                    $message = __('Subscription payment created successfully');
                }

                return $this->sendResponse(true, $message, $item);
            });
        } catch (Throwable $e) {
            logStore('SubscriptionPaymentService storeOrUpdateSubscriptionPayment', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function deleteSubscriptionPayment($id): array
    {
        $item = $this->subscriptionPaymentRepository->find((int) $id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        $this->subscriptionPaymentRepository->delete((int) $id);
        return $this->sendResponse(true, __('Data deleted successfully'));
    }

    public function paymentReportData($request): array
    {
        $tenantId = (int) ($request->tenant_id ?? 0);

        $verifiedPayments = DB::table('subscription_payments')
            ->when($tenantId > 0, fn ($query) => $query->where('tenant_id', $tenantId))
            ->where('status', 'verified');

        $totalIncome = (float) ((clone $verifiedPayments)->sum('amount') ?? 0);
        $totalTransactions = (int) ((clone $verifiedPayments)->count('id') ?? 0);

        $methodBreakdown = DB::table('subscription_payments')
            ->leftJoin('payment_methods', 'payment_methods.id', '=', 'subscription_payments.payment_method_id')
            ->when($tenantId > 0, fn ($query) => $query->where('subscription_payments.tenant_id', $tenantId))
            ->where('subscription_payments.status', 'verified')
            ->groupBy('subscription_payments.payment_method_id', 'payment_methods.name')
            ->orderByDesc(DB::raw('SUM(subscription_payments.amount)'))
            ->get([
                'subscription_payments.payment_method_id',
                'payment_methods.name as payment_method_name',
                DB::raw('COUNT(subscription_payments.id) as total_payments'),
                DB::raw('COALESCE(SUM(subscription_payments.amount), 0) as total_amount'),
            ]);

        $verifiedSubQuery = DB::table('subscription_payments')
            ->select([
                'subscription_id',
                DB::raw('COALESCE(SUM(amount), 0) as paid_amount'),
            ])
            ->where('status', 'verified')
            ->groupBy('subscription_id');

        $subscriptionBase = DB::table('subscriptions as s')
            ->leftJoin('tenants as t', 't.id', '=', 's.tenant_id')
            ->leftJoin('plans as p', 'p.id', '=', 's.plan_id')
            ->leftJoin('plan_pricings as pp', 'pp.id', '=', 's.plan_pricing_id')
            ->leftJoinSub($verifiedSubQuery, 'sp', function ($join) {
                $join->on('sp.subscription_id', '=', 's.id');
            })
            ->when($tenantId > 0, fn ($query) => $query->where('s.tenant_id', $tenantId))
            ->whereIn('s.status', ['trialing', 'active', 'past_due', 'expired'])
            ->select([
                's.id as subscription_id',
                's.tenant_id',
                't.company_name',
                't.company_username',
                'p.name as plan_name',
                DB::raw('COALESCE(pp.final_amount, 0) as due_amount'),
                DB::raw('COALESCE(sp.paid_amount, 0) as paid_amount'),
                DB::raw('(COALESCE(pp.final_amount, 0) - COALESCE(sp.paid_amount, 0)) as remaining_amount'),
            ]);

        $paidCount = (clone $subscriptionBase)
            ->whereRaw('COALESCE(sp.paid_amount, 0) >= COALESCE(pp.final_amount, 0)')
            ->count();

        $dueCount = (clone $subscriptionBase)
            ->whereRaw('COALESCE(sp.paid_amount, 0) < COALESCE(pp.final_amount, 0)')
            ->count();

        $paidSubscriptions = (clone $subscriptionBase)
            ->whereRaw('COALESCE(sp.paid_amount, 0) >= COALESCE(pp.final_amount, 0)')
            ->orderByDesc('s.id')
            ->limit(10)
            ->get();

        $dueSubscriptions = (clone $subscriptionBase)
            ->whereRaw('COALESCE(sp.paid_amount, 0) < COALESCE(pp.final_amount, 0)')
            ->orderByDesc('s.id')
            ->limit(10)
            ->get();

        $tenants = Tenant::query()
            ->orderBy('company_name')
            ->get(['id', 'company_name', 'company_username']);

        return $this->sendResponse(true, '', [
            'summary' => [
                'paid_subscriptions' => $paidCount,
                'due_subscriptions' => $dueCount,
                'total_income' => $totalIncome,
                'total_verified_transactions' => $totalTransactions,
            ],
            'tenants' => $tenants,
            'method_breakdown' => $methodBreakdown,
            'paid_subscriptions' => $paidSubscriptions,
            'due_subscriptions' => $dueSubscriptions,
        ]);
    }

    protected function buildMethodDetails(array $input): ?array
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
