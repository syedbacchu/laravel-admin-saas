<x-layout.default>
@section('title', $pageTitle)

    <div class="mt-8 bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
            <h5 class="text-2xl font-bold text-gray-800">{{ $pageTitle ?? __('Payment Report') }}</h5>
            <a href="{{ route('subscriptionPayment.list') }}" class="btn btn-outline-primary">
                {{ __('Back To Payment List') }}
            </a>
        </div>

        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
            <div>
                <label>{{ __('Tenant') }}</label>
                <select name="tenant_id" class="form-select">
                    <option value="">{{ __('All Tenants') }}</option>
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}" {{ (string) request('tenant_id') === (string) $tenant->id ? 'selected' : '' }}>
                            {{ $tenant->company_name }} ({{ $tenant->company_username }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2 flex items-end">
                <button type="submit" class="btn btn-secondary">{{ __('Filter Report') }}</button>
            </div>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
            <div class="panel bg-emerald-50 border border-emerald-100">
                <p class="text-xs text-gray-600">{{ __('Total Income') }}</p>
                <h4 class="text-2xl font-bold text-emerald-700">{{ number_format((float) $summary['total_income'], 2) }}</h4>
            </div>
            <div class="panel bg-blue-50 border border-blue-100">
                <p class="text-xs text-gray-600">{{ __('Verified Transactions') }}</p>
                <h4 class="text-2xl font-bold text-blue-700">{{ (int) $summary['total_verified_transactions'] }}</h4>
            </div>
            <div class="panel bg-green-50 border border-green-100">
                <p class="text-xs text-gray-600">{{ __('Paid Subscriptions') }}</p>
                <h4 class="text-2xl font-bold text-green-700">{{ (int) $summary['paid_subscriptions'] }}</h4>
            </div>
            <div class="panel bg-amber-50 border border-amber-100">
                <p class="text-xs text-gray-600">{{ __('Due Subscriptions') }}</p>
                <h4 class="text-2xl font-bold text-amber-700">{{ (int) $summary['due_subscriptions'] }}</h4>
            </div>
        </div>

        <div class="panel mb-8">
            <h6 class="text-lg font-semibold mb-4">{{ __('Payment Method Breakdown') }}</h6>
            <div class="overflow-x-auto">
                <table class="table-striped w-full">
                    <thead>
                    <tr>
                        <th>{{ __('Method') }}</th>
                        <th>{{ __('Total Payments') }}</th>
                        <th>{{ __('Total Amount') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($method_breakdown as $row)
                        <tr>
                            <td>{{ $row->payment_method_name ?? '-' }}</td>
                            <td>{{ (int) $row->total_payments }}</td>
                            <td>{{ number_format((float) $row->total_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-gray-500 py-4">{{ __('No verified payment found') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="panel">
                <h6 class="text-lg font-semibold mb-4 text-green-700">{{ __('Paid Subscriptions') }}</h6>
                <div class="overflow-x-auto">
                    <table class="table-striped w-full">
                        <thead>
                        <tr>
                            <th>{{ __('Subscription') }}</th>
                            <th>{{ __('Tenant') }}</th>
                            <th>{{ __('Due') }}</th>
                            <th>{{ __('Paid') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($paid_subscriptions as $row)
                            <tr>
                                <td>#{{ $row->subscription_id }}<br><small>{{ $row->plan_name ?? '-' }}</small></td>
                                <td>{{ $row->company_name }}<br><small>{{ $row->company_username }}</small></td>
                                <td>{{ number_format((float) $row->due_amount, 2) }}</td>
                                <td>{{ number_format((float) $row->paid_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-gray-500 py-4">{{ __('No paid subscription found') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel">
                <h6 class="text-lg font-semibold mb-4 text-amber-700">{{ __('Due Subscriptions') }}</h6>
                <div class="overflow-x-auto">
                    <table class="table-striped w-full">
                        <thead>
                        <tr>
                            <th>{{ __('Subscription') }}</th>
                            <th>{{ __('Tenant') }}</th>
                            <th>{{ __('Due') }}</th>
                            <th>{{ __('Remaining') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($due_subscriptions as $row)
                            <tr>
                                <td>#{{ $row->subscription_id }}<br><small>{{ $row->plan_name ?? '-' }}</small></td>
                                <td>{{ $row->company_name }}<br><small>{{ $row->company_username }}</small></td>
                                <td>{{ number_format((float) $row->due_amount, 2) }}</td>
                                <td>{{ number_format((float) $row->remaining_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-gray-500 py-4">{{ __('No due subscription found') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layout.default>

