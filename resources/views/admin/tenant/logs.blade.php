<x-layout.default>
    @section('title', $pageTitle)
    <div class="mt-8 bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
            <div>
                <h5 class="text-2xl font-bold text-gray-800">{{ $pageTitle ?? __('Migration Logs') }}</h5>
                @if(isset($tenant))
                <p class="text-gray-600 mt-1">{{ $tenant->company_name }} ({{ $tenant->company_username }})</p>
                @endif
            </div>

            <a href="{{ route('tenant.list') }}"
               class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-gray-600 to-gray-700 text-white font-medium rounded-lg shadow-md hover:shadow-lg hover:from-gray-700 hover:to-gray-800 focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('Back to Tenants') }}
            </a>
        </div>

        @if(isset($logs) && count($logs) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('ID') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Type') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Performed By') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Date') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Execution Time') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Migrations Run') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Reason') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($logs as $log)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $log['id'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($log['migration_type'] === 'fresh')
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700 font-semibold">Fresh</span>
                            @else
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700 font-semibold">Migrate</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($log['status'] === 'completed')
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700 font-semibold">{{ __('Completed') }}</span>
                            @elseif($log['status'] === 'failed')
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700 font-semibold">{{ __('Failed') }}</span>
                            @elseif($log['status'] === 'running')
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700 font-semibold">{{ __('Running') }}</span>
                            @else
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700 font-semibold">{{ __('Pending') }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if(isset($log['performed_by']) && $log['performed_by'])
                                @if(isset($log['performed_by']['name']))
                                <div>
                                    <span class="font-medium">{{ $log['performed_by']['name'] }}</span>
                                    <br>
                                    <span class="text-xs text-gray-500">ID: {{ $log['performed_by']['id'] }}</span>
                                </div>
                                @else
                                <span class="font-medium">User ID: {{ $log['performed_by'] }}</span>
                                @endif
                            @else
                            <span class="text-gray-400">{{ __('System') }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if(isset($log['created_at']))
                            {{ \Carbon\Carbon::parse($log['created_at'])->format('M d, Y H:i') }}
                            @else
                            -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if(isset($log['execution_time']) && $log['execution_time'])
                            <span class="font-medium">{{ number_format($log['execution_time'], 2) }}s</span>
                            @else
                            -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if(isset($log['migrations_run']) && $log['migrations_run'])
                            <span class="font-medium">{{ $log['migrations_run'] }}</span>
                            @else
                            -
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                            @if(isset($log['reason']) && $log['reason'])
                            {{ $log['reason'] }}
                            @else
                            -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button onclick="showLogDetails({{ $log['id'] }})" class="text-blue-600 hover:text-blue-900 font-medium">
                                {{ __('View Details') }}
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-12">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No Migration Logs') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ __('No migration logs found for this tenant.') }}</p>
        </div>
        @endif
    </div>

    @if(isset($logs) && count($logs) > 0)
    <script>
        const logData = @json($logs);

        function showLogDetails(logId) {
            const log = logData.find(l => l.id === logId);
            if (!log) return;

            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
            modal.onclick = function(e) {
                if (e.target === modal) {
                    modal.remove();
                }
            };

            modal.innerHTML = `
                <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-xl bg-white">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Migration Log Details #${log.id}</h3>
                        <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Migration Type</label>
                                <p class="mt-1 text-sm text-gray-900">${log.migration_type === 'fresh' ? 'Fresh Migration' : 'Regular Migration'}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <p class="mt-1 text-sm text-gray-900 capitalize">${log.status}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Command</label>
                                <p class="mt-1 text-sm text-gray-900 font-mono bg-gray-100 px-2 py-1 rounded">${log.command || '-'}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Execution Time</label>
                                <p class="mt-1 text-sm text-gray-900">${log.execution_time ? number_format(log.execution_time, 2) + 's' : '-'}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Migrations Run</label>
                                <p class="mt-1 text-sm text-gray-900">${log.migrations_run || '-'}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Started At</label>
                                <p class="mt-1 text-sm text-gray-900">${log.started_at ? new Date(log.started_at).toLocaleString() : '-'}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Completed At</label>
                                <p class="mt-1 text-sm text-gray-900">${log.completed_at ? new Date(log.completed_at).toLocaleString() : '-'}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Performed By</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    ${log.performed_by ?
                                        (log.performed_by.name ?
                                            log.performed_by.name + ' (ID: ' + log.performed_by.id + ')' :
                                            'User ID: ' + log.performed_by) :
                                        'System'}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">IP Address</label>
                                <p class="mt-1 text-sm text-gray-900">${log.ip_address || '-'}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Created At</label>
                                <p class="mt-1 text-sm text-gray-900">${log.created_at ? new Date(log.created_at).toLocaleString() : '-'}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Reason</label>
                            <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded">${log.reason || 'No reason provided'}</p>
                        </div>

                        ${log.output ? `
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Output</label>
                            <pre class="mt-1 text-sm text-gray-900 bg-gray-900 text-green-400 p-3 rounded overflow-x-auto" style="max-height: 200px;">${log.output}</pre>
                        </div>
                        ` : ''}

                        ${log.error_message ? `
                        <div>
                            <label class="block text-sm font-medium text-gray-700 text-red-600">Error Message</label>
                            <p class="mt-1 text-sm text-red-600 bg-red-50 px-3 py-2 rounded">${log.error_message}</p>
                        </div>
                        ` : ''}

                        <div>
                            <label class="block text-sm font-medium text-gray-700">User Agent</label>
                            <p class="mt-1 text-sm text-gray-500 bg-gray-50 px-3 py-2 rounded break-all">${log.user_agent || '-'}</p>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);
        }
    </script>
    @endif
</x-layout.default>