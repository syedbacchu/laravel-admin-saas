<x-layout.default>
    @section('title', $pageTitle)

    <div class="mt-8 bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
            <h5 class="text-2xl font-bold text-gray-800">{{ $pageTitle ?? __('Title') }}</h5>

            <a href="{{ route('user.create') }}"
               class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-medium rounded-lg shadow-md hover:shadow-lg hover:from-indigo-700 hover:to-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4v16m8-8H4" />
                </svg>
                {{__('Create User')}}
            </a>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">

            <x-common.datatable
                id="itemsTable"
                ajax="{{ route('user.list') }}"
                :columns="[
                    ['data' => 'name', 'name' => 'name', 'title' => 'Name'],
                    ['data' => 'phone', 'name' => 'phone', 'title' => 'Mobile'],
                    ['data' => 'role_module', 'name' => 'role_module', 'title' => 'Role'],
                    ['data' => 'status', 'name' => 'status', 'title' => 'Status'],
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
                        'name' => 'role_module',
                        'label' => 'Role Module',
                        'options' => [
                            '' => 'All',
                            '1' => 'Super Admin',
                            '2' => 'Admin',
                            '3' => 'User',
                        ]
                    ],
                    [
                        'type' => 'select',
                        'name' => 'status',
                        'label' => 'Active Status',
                        'options' => [
                            '' => 'All',
                            '1' => 'Active',
                            '0' => 'Inactive',
                            '2' => 'Deleted',
                            '3' => 'Suspended',
                        ]
                    ],
                    [
                        'type' => 'select',
                        'name' => 'role',
                        'label' => 'Role',
                        'options' => [
                            '' => 'All',
                            '1' => 'Admin',
                            '2' => 'User',
                        ]
                    ],
                    [
                        'type' => 'date',
                        'name' => 'created_at',
                        'label' => 'Created Date'
                    ],
                    [
                        'type' => 'daterange',
                        'name' => 'created_range',
                        'label' => 'Created Range'
                    ]
                ]"
                :enableSearch="false"
            />

        </div>
    </div>



</x-layout.default>
