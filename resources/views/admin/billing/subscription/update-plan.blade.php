<x-layout.default>
@section('title', $pageTitle)

<div class="panel mt-8">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('subscription.list') }}" class="text-gray-500 hover:text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l-7 7m7-7v18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ __('Update Subscription Features') }}</h1>
                <p class="text-gray-500">{{ __('Manage features for') }} {{ $subscription->tenant->company_name }}</p>
            </div>
        </div>
    </div>

    <!-- Subscription Info -->
    <div class="panel border border-gray-200 mb-6">
        <div class="p-4 bg-gray-50 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Company') }}</label>
                    <div class="font-semibold text-gray-900">{{ $subscription->tenant->company_name }}</div>
                    <div class="text-sm text-gray-500">{{ $subscription->tenant->company_username }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Current Plan') }}</label>
                    <div class="font-semibold text-gray-900">{{ $subscription->plan->name ?? 'N/A' }}</div>
                    <div class="text-sm text-gray-500">{{ __('Plan ID') }}: {{ $subscription->plan_id }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Status') }}</label>
                    <div class="font-semibold @if($subscription->status === 'active') text-green-600 @else text-orange-600 @endif">
                        {{ ucfirst($subscription->status) }}
                    </div>
                    <div class="text-sm text-gray-500">{{ $subscription->starts_at->format('M d, Y') }} - {{ $subscription->ends_at->format('M d, Y') }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Auto Renew') }}</label>
                    <div class="font-semibold @if($subscription->auto_renew) text-green-600 @else text-red-600 @endif">
                        {{ $subscription->auto_renew ? 'Enabled' : 'Disabled' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Form -->
    <form method="POST" action="{{ route('subscription.updatePlanSave', $subscription->id) }}">
        @csrf

        @foreach($featuresByCategory as $category => $categoryData)
            <div class="panel border border-gray-200 mb-6" x-data="{ selectAll: false, updateSelectAll() { const checkboxes = $el.closest('.panel').querySelectorAll('.feature-checkbox'); selectAll = Array.from(checkboxes).every(cb => cb.checked) && checkboxes.length > 0; } }" x-init="updateSelectAll()">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">{{ $categoryData['name'] }}</h2>
                            <span class="text-sm text-gray-500">{{ count($categoryData['features']) }} {{ __('features') }}</span>
                        </div>
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="checkbox" class="form-checkbox" x-model="selectAll" @change="$el.closest('.panel').querySelectorAll('.feature-checkbox').forEach(cb => cb.checked = $el.checked)">
                            <span>{{ __('Select All') }}</span>
                        </label>
                    </div>
                </div>

                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2 text-left bg-gray-50">{{ __('Enable') }}</th>
                                    <th class="px-3 py-2 text-left bg-gray-50">{{ __('Feature Name') }}</th>
                                    <th class="px-3 py-2 text-left bg-gray-50">{{ __('Feature Key') }}</th>
                                    <th class="px-3 py-2 text-left bg-gray-50">{{ __('Type') }}</th>
                                    <th class="px-3 py-2 text-left bg-gray-50">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categoryData['features'] as $feature)
                                    <tr class="border-t {{ $loop->even ? 'bg-gray-50 dark:bg-gray-800/50' : '' }}">
                                        <td class="px-3 py-2">
                                            <input type="checkbox"
                                                   class="feature-checkbox"
                                                   name="features[]"
                                                   value="{{ $feature['key'] }}"
                                                   {{ $feature['enabled'] ? 'checked' : '' }}
                                                   @change="$el.closest('.panel').updateSelectAll()">
                                        </td>
                                        <td class="px-3 py-2">{{ $feature['name'] }}</td>
                                        <td class="px-3 py-2 font-mono text-xs">{{ $feature['key'] }}</td>
                                        <td class="px-3 py-2">{{ ucfirst($feature['type']) }}</td>
                                        <td class="px-3 py-2">
                                            @if($feature['source'] === 'plan')
                                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">{{ ucfirst($feature['source']) }}</span>
                                            @elseif($feature['source'] === 'snapshot')
                                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">{{ ucfirst($feature['source']) }}</span>
                                            @else
                                                <span class="px-2 py-1 text-xs bg-gray-200 text-gray-700 rounded-full">{{ ucfirst($feature['source']) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Action Buttons -->
        <div class="panel border border-gray-200 mt-6">
            <div class="p-4 flex justify-end gap-3">
                <a href="{{ route('subscription.list') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    {{ __('Save Changes') }}
                </button>
            </div>
        </div>
    </form>
</div>

</x-layout.default>
