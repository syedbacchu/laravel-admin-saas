<x-layout.default>
@section('title', $pageTitle)

<div class="mt-8 bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
        <h5 class="text-2xl font-bold text-gray-800">{{ $pageTitle ?? __('Database Backups') }}</h5>

        <a href="{{ route('databaseBackup.create') }}"
           class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-medium rounded-lg shadow-md hover:shadow-lg hover:from-indigo-700 hover:to-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4v16m8-8H4" />
            </svg>
            {{ __('Create Backup') }}
        </a>
    </div>

    <!-- Statistics & Info -->
    @if(isset($statistics))
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Total Backups -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-4 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">{{ __('Total Backups') }}</p>
                        <p class="text-2xl font-bold">{{ $statistics['total_backups'] ?? 0 }}</p>
                    </div>
                    <div class="bg-blue-400 bg-opacity-30 rounded-lg p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Size -->
            <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-4 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-emerald-100 text-sm font-medium">{{ __('Total Size') }}</p>
                        <p class="text-2xl font-bold">{{ $statistics['total_size'] ?? '0 bytes' }}</p>
                    </div>
                    <div class="bg-emerald-400 bg-opacity-30 rounded-lg p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Latest Backup -->
            <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl p-4 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-amber-100 text-sm font-medium">{{ __('Latest Backup') }}</p>
                        <p class="text-lg font-bold">
                            @if(isset($statistics['latest_backup']))
                                {{ $statistics['latest_backup']->backup_created_at->diffForHumans() }}
                            @else
                                {{ __('No backups') }}
                            @endif
                        </p>
                    </div>
                    <div class="bg-amber-400 bg-opacity-30 rounded-lg p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Security Notice -->
    <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">{{ __('Security Information') }}</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>{{ __('All database backups are password-protected ZIP files. Each backup requires a password to extract, providing additional security even if the file is downloaded.') }}</p>
                    <p class="mt-1">
                        <strong>{{ __('ZIP Password') }}:</strong>
                        <code class="bg-blue-100 px-2 py-1 rounded text-xs">{{ env('BACKUP_ZIP_PASSWORD', 'Set in .env file') }}</code>
                    </p>
                </div>
            </div>
        </div>
    </div>


    <!-- DataTable -->
    <div class="overflow-x-auto">
        <x-common.datatable
            id="itemsTable"
            ajax="{{ route('databaseBackup.list') }}"
            :columns="[
                ['data' => 'id', 'name' => 'id', 'title' => 'ID'],
                ['data' => 'file_name', 'name' => 'file_name', 'title' => 'File Name'],
                ['data' => 'database_name', 'name' => 'database_name', 'title' => 'Database'],
                ['data' => 'backup_created_at', 'name' => 'backup_created_at', 'title' => 'Created At'],
                ['data' => 'file_size', 'name' => 'file_size', 'title' => 'File Size'],
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
                    'name' => 'status',
                    'label' => 'Status',
                    'options' => [
                        '' => 'All',
                        '1' => 'Active',
                        '0' => 'Inactive'
                    ]
                ],
                [
                    'type' => 'date',
                    'name' => 'backup_created_at',
                    'label' => 'Created Date'
                ],
                [
                    'type' => 'daterange',
                    'name' => 'created_range',
                    'label' => 'Created Range'
                ]
            ]"
            :enableSearch="true"
        />
    </div>
</div>

</x-layout.default>
