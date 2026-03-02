<x-layout.default>
    @section('title', $pageTitle)

    @php
        $currentItem = $item ?? null;
        $selectedTenant = (string) old('tenant_id', $currentItem?->tenant_id ?? '');
        $selectedSubscription = (string) old('subscription_id', $currentItem?->subscription_id ?? '');
        $selectedMethod = (string) old('payment_method_id', $currentItem?->payment_method_id ?? '');
        $methodDetails = (array) old('method_details', $currentItem?->method_details ?? []);
        $selectedStatus = old('status', $currentItem?->status ?? 'pending');
    @endphp

    <div class="panel mt-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">{{ $pageTitle }}</h1>

        <form method="POST" action="{{ $currentItem ? route('subscriptionPayment.update', $currentItem->id) : route('subscriptionPayment.store') }}">
            @csrf
            @if($currentItem)
                @method('PUT')
                <input type="hidden" name="edit_id" value="{{ $currentItem->id }}">
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="mb-2">
                    <label>{{ __('Tenant') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <select id="tenant-select" name="tenant_id" class="form-select ltr:rounded-l-none rtl:rounded-r-none" required>
                            <option value="">{{ __('Select tenant') }}</option>
                            @foreach($tenants as $tenant)
                                <option value="{{ $tenant->id }}" {{ $selectedTenant === (string) $tenant->id ? 'selected' : '' }}>
                                    {{ $tenant->company_name }} ({{ $tenant->company_username }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-2">
                    <label>{{ __('Subscription') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <select id="subscription-select" name="subscription_id" class="form-select ltr:rounded-l-none rtl:rounded-r-none" required>
                            <option value="">{{ __('Select subscription') }}</option>
                            @foreach($subscriptions as $subscription)
                                @php
                                    $dueAmount = $subscription->pricing?->final_amount;
                                    $currency = $subscription->pricing?->currency ?? 'BDT';
                                @endphp
                                <option
                                    value="{{ $subscription->id }}"
                                    data-tenant-id="{{ $subscription->tenant_id }}"
                                    {{ $selectedSubscription === (string) $subscription->id ? 'selected' : '' }}
                                >
                                    #{{ $subscription->id }} - {{ $subscription->plan?->name ?? 'Plan' }}
                                    @if($dueAmount !== null)
                                        ({{ number_format((float) $dueAmount, 2) }} {{ $currency }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-2">
                    <label>{{ __('Payment Method') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <select id="payment-method-select" name="payment_method_id" class="form-select ltr:rounded-l-none rtl:rounded-r-none" required>
                            <option value="">{{ __('Select payment method') }}</option>
                            @foreach($payment_methods as $method)
                                <option
                                    value="{{ $method->id }}"
                                    data-code="{{ $method->code }}"
                                    data-details='@json($method->details_json)'
                                    {{ $selectedMethod === (string) $method->id ? 'selected' : '' }}
                                >
                                    {{ $method->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <small id="method-help" class="text-xs text-gray-500 mt-1 block"></small>
                </div>

                <div class="mb-2">
                    <label>{{ __('Amount') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input name="amount" type="number" min="0.01" step="0.01" value="{{ old('amount', isset($currentItem->amount) ? (float) $currentItem->amount : '') }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" required />
                    </div>
                </div>

                <div class="mb-2">
                    <label>{{ __('Currency') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input name="currency" type="text" value="{{ old('currency', $currentItem?->currency ?? 'BDT') }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                    </div>
                </div>

                <div class="mb-2">
                    <label>{{ __('Status') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <select name="status" class="form-select ltr:rounded-l-none rtl:rounded-r-none">
                            <option value="pending" {{ $selectedStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="verified" {{ $selectedStatus === 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="rejected" {{ $selectedStatus === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                </div>

                <div class="mb-2">
                    <label>{{ __('Paid At') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input
                            name="paid_at"
                            type="datetime-local"
                            value="{{ old('paid_at', isset($currentItem->paid_at) ? $currentItem->paid_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
                            class="form-input ltr:rounded-l-none rtl:rounded-r-none"
                        />
                    </div>
                </div>

                <div class="mb-2">
                    <label>{{ __('Payment Reference') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input name="payment_reference" type="text" value="{{ old('payment_reference', $currentItem?->payment_reference ?? '') }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                    </div>
                </div>
            </div>

            <h5 class="text-xl font-semi-bold text-gray-600 dark:text-gray-100 mt-8 mb-4">{{ __('Method Details') }}</h5>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="mb-2">
                    <label>{{ __('Mobile Number') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input name="method_details[mobile_number]" type="text" value="{{ $methodDetails['mobile_number'] ?? '' }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                    </div>
                </div>

                <div class="mb-2">
                    <label>{{ __('Account Number') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input name="method_details[account_number]" type="text" value="{{ $methodDetails['account_number'] ?? '' }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                    </div>
                </div>

                <div class="mb-2">
                    <label>{{ __('Bank Name') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input name="method_details[bank_name]" type="text" value="{{ $methodDetails['bank_name'] ?? '' }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                    </div>
                </div>

                <div class="mb-2">
                    <label>{{ __('Branch Name (Optional)') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input name="method_details[branch_name]" type="text" value="{{ $methodDetails['branch_name'] ?? '' }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                    </div>
                </div>

                <div class="mb-2 md:col-span-2">
                    <label>{{ __('Note') }}</label>
                    <textarea name="note" class="form-textarea min-h-[90px]">{{ old('note', $currentItem?->note ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-secondary">{{ __('Submit') }}</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tenantSelect = document.getElementById('tenant-select');
            const subscriptionSelect = document.getElementById('subscription-select');
            const methodSelect = document.getElementById('payment-method-select');
            const methodHelp = document.getElementById('method-help');

            function filterSubscriptionOptions() {
                const tenantId = tenantSelect.value;
                Array.from(subscriptionSelect.options).forEach(function (option) {
                    if (!option.value) {
                        option.hidden = false;
                        return;
                    }
                    const optionTenantId = option.getAttribute('data-tenant-id');
                    option.hidden = tenantId !== '' && optionTenantId !== tenantId;
                });

                const selected = subscriptionSelect.options[subscriptionSelect.selectedIndex];
                if (selected && selected.hidden) {
                    subscriptionSelect.value = '';
                }
            }

            function showMethodHelp() {
                const selected = methodSelect.options[methodSelect.selectedIndex];
                if (!selected || !selected.value) {
                    methodHelp.textContent = '';
                    return;
                }

                const detailsRaw = selected.getAttribute('data-details');
                if (!detailsRaw) {
                    methodHelp.textContent = '';
                    return;
                }

                try {
                    const details = JSON.parse(detailsRaw);
                    if (!details) {
                        methodHelp.textContent = '';
                        return;
                    }

                    const parts = [];
                    if (details.mobile_number) parts.push('Mobile: ' + details.mobile_number);
                    if (details.account_number) parts.push('Account: ' + details.account_number);
                    if (details.bank_name) parts.push('Bank: ' + details.bank_name);
                    if (details.branch_name) parts.push('Branch: ' + details.branch_name);

                    methodHelp.textContent = parts.join(' | ');
                } catch (e) {
                    methodHelp.textContent = '';
                }
            }

            tenantSelect.addEventListener('change', filterSubscriptionOptions);
            methodSelect.addEventListener('change', showMethodHelp);

            filterSubscriptionOptions();
            showMethodHelp();
        });
    </script>
</x-layout.default>
