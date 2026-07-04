<x-layout.default>
@section('title', $pageTitle)

<div class="mt-8 bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
        <h5 class="text-2xl font-bold text-gray-800">{{ $pageTitle ?? __('Create Database Backup') }}</h5>

        <a href="{{ route('databaseBackup.list') }}"
           class="inline-flex items-center px-5 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            {{ __('Back to List') }}
        </a>
    </div>

    <form action="{{ route('databaseBackup.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Backup Information -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">{{ __('Backup Information') }}</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>{{ __('This will create a password-protected ZIP backup of your main database. The process may take several minutes depending on database size.') }}</p>
                                <ul class="mt-2 space-y-1">
                                    <li><strong>{{ __('Database') }}:</strong> {{ $database_name }}</li>
                                    <li><strong>{{ __('Host') }}:</strong> {{ $database_host }}</li>
                                    <li><strong>{{ __('Password Protection') }}:</strong> {{ __('Enabled') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Important Notes -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">{{ __('Important Notes') }}</h3>
                            <ul class="mt-2 text-sm text-yellow-700 space-y-1">
                                <li>• {{ __('Backup creation may take several minutes for large databases') }}</li>
                                <li>• {{ __('Please do not close this page while backup is being created') }}</li>
                                <li>• {{ __('Backup files are stored in storage/app/backups/ directory') }}</li>
                                <li>• {{ __('Each backup is password-protected with ZIP encryption') }}</li>
                                <li>• {{ __('Make sure your PHP configuration allows sufficient execution time and memory') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Form Field -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('Backup Description') }}
                        </label>
                        <textarea
                            name="description"
                            id="description"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            placeholder="{{ __('Optional description for this backup') }}">{{ old('description') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Add notes about this backup (pre-update, migration, etc.)') }}</p>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center gap-3">
                    <button
                        type="submit"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-medium rounded-lg shadow-md hover:shadow-lg hover:from-indigo-700 hover:to-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        {{ __('Create Backup') }}
                    </button>
                    <a
                        href="{{ route('databaseBackup.list') }}"
                        class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        {{ __('Cancel') }}
                    </a>
                </div>
            </div>

            <!-- Sidebar Information -->
            <div class="space-y-6">
                <!-- Security Information -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">{{ __('Password Protected') }}</h3>
                            <div class="mt-2 text-sm text-green-700">
                                <p>{{ __('All backups are created as password-protected ZIP files. This ensures that:') }}</p>
                                <ul class="mt-2 space-y-1">
                                    <li>✓ {{ __('Unauthorized users cannot extract backup files') }}</li>
                                    <li>✓ {{ __('Downloaded backups remain encrypted') }}</li>
                                    <li>✓ {{ __('Additional layer of security beyond file permissions') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ZIP Password Display -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964a6 6 0 116.842-6.842L12 2.929zM13 7a2 2 0 012-2 2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">{{ __('ZIP Password') }}</h3>
                            <div class="mt-2">
                                <code class="bg-white px-3 py-2 rounded text-sm border border-blue-300">{{ env('BACKUP_ZIP_PASSWORD', 'Set in .env') }}</code>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuration Tip -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">{{ __('Configuration Tip') }}</h3>
                            <p class="mt-2 text-sm text-yellow-700">{{ __('Set a strong password in your .env file using BACKUP_ZIP_PASSWORD variable') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Best Practices -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-800">{{ __('Best Practices') }}</h3>
                            <ul class="mt-2 text-sm text-gray-700 space-y-2">
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    {{ __('Create backups before major updates') }}
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    {{ __('Download and store backups off-site') }}
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    {{ __('Keep ZIP passwords secure') }}
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    {{ __('Regular backup schedule recommended') }}
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    {{ __('Test backup restoration process') }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

</x-layout.default>