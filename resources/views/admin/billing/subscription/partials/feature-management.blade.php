<!-- Subscription Feature Management Button -->
<div class="flex gap-2">
    <!-- View Features Button -->
    <button onclick="viewSubscriptionFeatures({{ $subscription->id }})"
            class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        Features
    </button>

    <!-- Refresh from Plan Button -->
    <button onclick="refreshFromPlan({{ $subscription->id }})"
            class="inline-flex items-center px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        Refresh
    </button>
</div>

<!-- Feature Management Modal -->
<div id="featureModal{{ $subscription->id }}" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Subscription Features</h3>
                <button onclick="closeFeatureModal({{ $subscription->id }})" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="p-6">
            <!-- Add Feature Form -->
            <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                <h4 class="font-semibold mb-3">Add Feature</h4>
                <div class="flex gap-3">
                    <select id="featureSelect{{ $subscription->id }}" class="flex-1 rounded-lg border-gray-300">
                        <option value="">Select a feature...</option>
                        <option value="helper.management">Helper Management</option>
                        <option value="employee.management">Employee Management</option>
                        <option value="payroll.salary_commission">Payroll Management</option>
                        <option value="reports.advanced_analytics">Advanced Reports</option>
                        <option value="fuel.intelligence">Fuel Intelligence</option>
                        <option value="data.file_management">File Management</option>
                    </select>
                    <select id="featureValue{{ $subscription->id }}" class="w-32 rounded-lg border-gray-300">
                        <option value="true">Enabled</option>
                        <option value="false">Disabled</option>
                    </select>
                    <button onclick="addFeatureToSubscription({{ $subscription->id }})"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Add
                    </button>
                </div>
            </div>

            <!-- Features List -->
            <div id="featuresList{{ $subscription->id }}" class="space-y-2">
                <div class="text-center text-gray-500 py-4">
                    Loading features...
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function viewSubscriptionFeatures(subscriptionId) {
    document.getElementById(`featureModal${subscriptionId}`).classList.remove('hidden');
    document.getElementById(`featureModal${subscriptionId}`).classList.add('flex');

    try {
        const response = await fetch(`/admin/subscription-features/${subscriptionId}`);
        const data = await response.json();

        if (data.success) {
            renderFeaturesList(subscriptionId, data.data.features);
        }
    } catch (error) {
        console.error('Error loading features:', error);
    }
}

function renderFeaturesList(subscriptionId, features) {
    const container = document.getElementById(`featuresList${subscriptionId}`);

    if (features.length === 0) {
        container.innerHTML = '<div class="text-center text-gray-500 py-4">No features found</div>';
        return;
    }

    container.innerHTML = features.map(feature => `
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <div>
                <div class="font-medium">${feature.key}</div>
                <div class="text-sm text-gray-500">
                    Type: ${feature.type} | Source: ${feature.source}
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-full text-sm ${feature.value ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                    ${feature.value ? 'Active' : 'Inactive'}
                </span>
                ${feature.source === 'snapshot' ? `
                    <button onclick="removeFeatureFromSubscription(${subscriptionId}, '${feature.key}')"
                            class="p-1 text-red-600 hover:bg-red-100 rounded">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                ` : ''}
            </div>
        </div>
    `).join('');
}

async function addFeatureToSubscription(subscriptionId) {
    const featureKey = document.getElementById(`featureSelect${subscriptionId}`).value;
    const featureValue = document.getElementById(`featureValue${subscriptionId}`).value === 'true';

    if (!featureKey) {
        alert('Please select a feature');
        return;
    }

    try {
        const response = await fetch(`/admin/subscription-features/${subscriptionId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ feature_key: featureKey, value: featureValue })
        });

        const data = await response.json();

        if (data.success) {
            alert('Feature added successfully!');
            viewSubscriptionFeatures(subscriptionId); // Reload list
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error adding feature: ' + error.message);
    }
}

async function removeFeatureFromSubscription(subscriptionId, featureKey) {
    if (!confirm(`Remove ${featureKey} from this subscription?`)) return;

    try {
        const response = await fetch(`/admin/subscription-features/${subscriptionId}/${featureKey}`, {
            method: 'DELETE'
        });

        const data = await response.json();

        if (data.success) {
            alert('Feature removed successfully!');
            viewSubscriptionFeatures(subscriptionId); // Reload list
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error removing feature: ' + error.message);
    }
}

async function refreshFromPlan(subscriptionId) {
    if (!confirm('Refresh all features from the current plan? This will overwrite custom features.')) return;

    try {
        const response = await fetch(`/admin/subscription-features/${subscriptionId}/refresh-from-plan`, {
            method: 'POST'
        });

        const data = await response.json();

        if (data.success) {
            alert('Subscription refreshed from plan successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error refreshing: ' + error.message);
    }
}

function closeFeatureModal(subscriptionId) {
    document.getElementById(`featureModal${subscriptionId}`).classList.add('hidden');
    document.getElementById(`featureModal${subscriptionId}`).classList.remove('flex');
}
</script>
