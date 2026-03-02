<x-layout.default>
@section('title', $pageTitle)
<link rel="stylesheet" href="{{ asset('assets/common/datatables/jquery.dataTables.min.css') }}">
<script src="{{ asset('assets/common/jquery-3.7.0.min.js') }}"></script>
<script src="{{ asset('assets/common/datatables/jquery.dataTables.min.js') }}"></script>

    <div class="mt-8 bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
            <h5 class="text-2xl font-bold text-gray-800">{{ $pageTitle ?? __('Subscription List') }}</h5>

            <a href="{{ route('subscription.create') }}"
               class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-medium rounded-lg shadow-md hover:shadow-lg hover:from-indigo-700 hover:to-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Create Subscription') }}
            </a>
        </div>

        <div class="overflow-x-auto">
            <x-common.datatable
                id="itemsTable"
                ajax="{{ route('subscription.list') }}"
                :columns="[
                    ['data' => 'tenant', 'name' => 'company_name', 'title' => 'Tenant'],
                    ['data' => 'plan', 'name' => 'plan_name', 'title' => 'Plan'],
                    ['data' => 'status', 'name' => 'status', 'title' => 'Status'],
                    ['data' => 'starts_at', 'name' => 'starts_at', 'title' => 'Starts'],
                    ['data' => 'ends_at', 'name' => 'ends_at', 'title' => 'Ends'],
                    ['data' => 'auto_renew', 'name' => 'auto_renew', 'title' => 'Auto Renew'],
                    [
                        'data' => 'actions',
                        'title' => 'Actions',
                        'orderable' => false,
                        'searchable' => false
                    ],
                ]"
                :filters="[
                    [
                        'type' => 'select',
                        'name' => 'status',
                        'label' => 'Status',
                        'options' => [
                            '' => 'All',
                            'trialing' => 'Trialing',
                            'active' => 'Active',
                            'past_due' => 'Past Due',
                            'canceled' => 'Canceled',
                            'expired' => 'Expired'
                        ]
                    ]
                ]"
                :enableSearch="false"
            />
        </div>
    </div>
</x-layout.default>
