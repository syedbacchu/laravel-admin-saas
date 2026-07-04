<!-- Apply Plan to All Subscriptions Button -->
<div class="flex gap-2 mt-4">
    <button onclick="showApplyToAllModal({{ $plan->id }})"
            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-medium rounded-lg shadow-md hover:shadow-lg hover:from-green-700 hover:to-emerald-700 transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
        </svg>
        Apply to All Subscriptions
    </button>

    <button onclick="showPlanSubscriptions({{ $plan->id }})"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg shadow-md hover:shadow-lg hover:bg-blue-700 transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        View Subscriptions
    </button>
</div>

<!-- Apply to All Modal -->
<div id="applyToAllModal{{ $plan->id }}" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-lg w-full">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Apply Plan to All Subscriptions</h3>
                <button onclick="closeApplyToAllModal({{ $plan->id }})" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="p-6">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Update Mode
                </label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="radio" name="updateMode{{ $plan->id }}" value="force" checked class="mr-2">
                        <div>
                            <span class="font-medium">Force Refresh</span>
                            <p class="text-sm text-gray-500">Overwrite all subscription features with current plan features</p>
                        </div>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="updateMode{{ $plan->id }}" value="add_missing" class="mr-2">
                        <div>
                            <span class="font-medium">Add Missing Only</span>
                            <p class="text-sm text-gray-500">Only add features that subscriptions don't already have</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div class="text-sm text-yellow-800">
                        <strong>Warning:</strong> This will affect all active subscriptions using this plan.
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button onclick="applyToAllSubscriptions({{ $plan->id }})"
                        class="flex-1 px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-medium rounded-lg hover:from-green-700 hover:to-emerald-700">
                    Apply to All
                </button>
                <button onclick="closeApplyToAllModal({{ $plan->id }})"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Subscriptions List Modal -->
<div id="subscriptionsModal{{ $plan->id }}" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Plan Subscriptions</h3>
                <button onclick="closeSubscriptionsModal({{ $plan->id }})" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="p-6">
            <div id="subscriptionsList{{ $plan->id }}" class="space-y-2">
                <div class="text-center text-gray-500 py-4">
                    Loading subscriptions...
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showApplyToAllModal(planId) {
    document.getElementById(`applyToAllModal${planId}`).classList.remove('hidden');
    document.getElementById(`applyToAllModal${planId}`).classList.add('flex');
}

function closeApplyToAllModal(planId) {
    document.getElementById(`applyToAllModal${planId}`).classList.add('hidden');
    document.getElementById(`applyToAllModal${planId}`).classList.remove('flex');
}

async function applyToAllSubscriptions(planId) {
    const updateMode = document.querySelector(`input[name="updateMode${planId}"]:checked`).value;
    const forceRefresh = updateMode === 'force';

    if (!confirm(`Are you sure you want to apply this plan to all subscriptions? (${updateMode})`)) {
        return;
    }

    try {
        const response = await fetch('/admin/subscription-features/apply-to-all', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ plan_id: planId, force_refresh: forceRefresh })
        });

        const data = await response.json();

        if (data.success) {
            alert(`Plan applied successfully!\nTotal: ${data.data.total}\nUpdated: ${data.data.updated}\nFailed: ${data.data.failed}`);
            closeApplyToAllModal(planId);
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error applying plan: ' + error.message);
    }
}

async function showPlanSubscriptions(planId) {
    document.getElementById(`subscriptionsModal${planId}`).classList.remove('hidden');
    document.getElementById(`subscriptionsModal${planId}`).classList.add('flex');

    try {
        const response = await fetch(`/admin/subscription-features/plan/${planId}`);
        const data = await response.json();

        if (data.success) {
            renderSubscriptionsList(planId, data.data.subscriptions);
        }
    } catch (error) {
        console.error('Error loading subscriptions:', error);
    }
}

function renderSubscriptionsList(planId, subscriptions) {
    const container = document.getElementById(`subscriptionsList${planId}`);

    if (subscriptions.length === 0) {
        container.innerHTML = '<div class="text-center text-gray-500 py-4">No subscriptions found for this plan</div>';
        return;
    }

    container.innerHTML = `
        <div class="mb-4 text-sm text-gray-600">
            Total: ${subscriptions.length} subscriptions
        </div>
        <div class="space-y-2">
            ${subscriptions.map(sub => `
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <div class="font-medium">${sub.tenant?.company_name || 'Unknown'}</div>
                        <div class="text-sm text-gray-500">
                            Status: ${sub.status} | Ends: ${sub.ends_at}
                        </div>
                    </div>
                    <div class="text-sm">
                        <span class="px-2 py-1 rounded-full ${sub.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                            ${sub.status}
                        </span>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

function closeSubscriptionsModal(planId) {
    document.getElementById(`subscriptionsModal${planId}`).classList.add('hidden');
    document.getElementById(`subscriptionsModal${planId}`).classList.remove('flex');
}
</script>
