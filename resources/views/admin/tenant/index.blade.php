<x-layout.default>
    @section('title', $pageTitle)
    <link rel="stylesheet" href="{{ asset('assets/common/datatables/jquery.dataTables.min.css') }}">
    <script src="{{ asset('assets/common/jquery-3.7.0.min.js') }}"></script>
    <script src="{{ asset('assets/common/datatables/jquery.dataTables.min.js') }}"></script>

    <div class="mt-8 bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
            <h5 class="text-2xl font-bold text-gray-800">{{ $pageTitle ?? __('Tenant List') }}</h5>

            <a href="{{ route('tenant.create') }}"
               class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-medium rounded-lg shadow-md hover:shadow-lg hover:from-indigo-700 hover:to-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Create Tenant') }}
            </a>
        </div>

        <div class="overflow-x-auto">
            <x-common.datatable
                id="itemsTable"
                ajax="{{ route('tenant.list') }}"
                :columns="[
                    ['data' => 'company', 'name' => 'company_name', 'title' => 'Company'],
                    ['data' => 'owner', 'name' => 'owner_name', 'title' => 'Owner'],
                    ['data' => 'db_name', 'name' => 'db_name', 'title' => 'Database'],
                    ['data' => 'status', 'name' => 'status', 'title' => 'Status'],
                    ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Created'],
                    ['data' => 'actions', 'name' => 'actions', 'title' => 'Actions', 'orderable' => false, 'searchable' => false],
                ]"
                :filters="[
                    [
                        'type' => 'select',
                        'name' => 'status',
                        'label' => 'Status',
                        'options' => [
                            '' => 'All',
                            'active' => 'Active',
                            'provisioning' => 'Provisioning',
                            'failed' => 'Failed',
                            'suspended' => 'Suspended',
                        ]
                    ]
                ]"
                :enableSearch="false"
            />
        </div>
    </div>

    <script>
        function backupTenant(backupUrl, companyName) {
            event.preventDefault();
            event.stopPropagation();

            if (!confirm('Are you sure you want to backup the database for ' + companyName + '?')) {
                return false;
            }

            // Show loading state
            const btn = event.target.closest('button');
            const originalContent = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Backing up...';

            // Send AJAX request for backup
            $.ajax({
                url: backupUrl,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    btn.disabled = false;
                    btn.innerHTML = originalContent;

                    if (response.success) {
                        alert('Backup completed successfully!');
                    } else {
                        alert('Backup failed: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                    alert('Backup failed: ' + (xhr.responseJSON?.message || 'Server error'));
                }
            });
        }

        function migrateTenant(migrateUrl, companyName) {
            event.preventDefault();
            event.stopPropagation();

            // Ask for reason
            const reason = prompt('Please provide the reason for running migrations for ' + companyName + ':');

            if (reason === null) {
                return false; // User cancelled
            }

            if (reason.trim() === '') {
                alert('Please provide a reason for running migrations.');
                return false;
            }

            if (!confirm('Are you sure you want to run migrations for ' + companyName + '?')) {
                return false;
            }

            // Show loading state
            const btn = event.target.closest('button');
            const originalContent = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Migrating...';

            // Send AJAX request for migration
            $.ajax({
                url: migrateUrl,
                type: 'POST',
                data: {
                    reason: reason
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    btn.disabled = false;
                    btn.innerHTML = originalContent;

                    if (response.success) {
                        let message = 'Migration completed successfully!';
                        if (response.data && response.data.migrations_run) {
                            message += '\nMigrations run: ' + response.data.migrations_run;
                        }
                        alert(message);
                    } else {
                        alert('Migration failed: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                    alert('Migration failed: ' + (xhr.responseJSON?.message || 'Server error'));
                }
            });
        }

        function migrateTenantFresh(migrateFreshUrl, companyName) {
            event.preventDefault();
            event.stopPropagation();

            // Ask for reason
            const reason = prompt('Please provide the reason for running FRESH migrations for ' + companyName + ' (this will delete all data):');

            if (reason === null) {
                return false; // User cancelled
            }

            if (reason.trim() === '') {
                alert('Please provide a reason for running fresh migrations.');
                return false;
            }

            if (!confirm('WARNING: This will delete all data and re-create tables for ' + companyName + '. Are you sure you want to run fresh migration?')) {
                return false;
            }

            // Double confirmation for destructive operation
            if (!confirm('This action cannot be undone. All data will be permanently deleted. Continue?')) {
                return false;
            }

            // Show loading state
            const btn = event.target.closest('button');
            const originalContent = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Fresh migrating...';

            // Send AJAX request for fresh migration
            $.ajax({
                url: migrateFreshUrl,
                type: 'POST',
                data: {
                    reason: reason
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    btn.disabled = false;
                    btn.innerHTML = originalContent;

                    if (response.success) {
                        let message = 'Fresh migration completed successfully! Database has been reset.';
                        if (response.data && response.data.migrations_run) {
                            message += '\nMigrations run: ' + response.data.migrations_run;
                        }
                        alert(message);
                    } else {
                        alert('Fresh migration failed: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                    alert('Fresh migration failed: ' + (xhr.responseJSON?.message || 'Server error'));
                }
            });
        }
    </script>
</x-layout.default>
