<x-layout.default>
    @section('title', $pageTitle)

    <div class="panel mt-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">{{ $pageTitle }}</h1>

        <form method="POST" action="{{ isset($item) ? route('subscription.update', $item->id) : route('subscription.store') }}">
            @csrf
            @if(isset($item))
                @method('PUT')
                <input type="hidden" name="edit_id" value="{{ $item->id }}">
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="mb-2">
                    <label>{{ __('Tenant') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        @php $selectedTenant = (string) old('tenant_id', $item->tenant_id ?? ''); @endphp
                        <select name="tenant_id" class="form-select ltr:rounded-l-none rtl:rounded-r-none">
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
                    <label>{{ __('Plan') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        @php $selectedPlan = (string) old('plan_id', $item->plan_id ?? ''); @endphp
                        <select id="plan-select" name="plan_id" class="form-select ltr:rounded-l-none rtl:rounded-r-none">
                            <option value="">{{ __('Select plan') }}</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" {{ $selectedPlan === (string) $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-2">
                    <label>{{ __('Pricing Term') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        @php $selectedPricing = (string) old('plan_pricing_id', $item->plan_pricing_id ?? ''); @endphp
                        <select id="pricing-select" name="plan_pricing_id" class="form-select ltr:rounded-l-none rtl:rounded-r-none">
                            <option value="">{{ __('Select pricing') }}</option>
                            @foreach($pricings as $pricing)
                                <option
                                    value="{{ $pricing->id }}"
                                    data-plan-id="{{ $pricing->plan_id }}"
                                    {{ $selectedPricing === (string) $pricing->id ? 'selected' : '' }}
                                >
                                    {{ $pricing->plan?->name ?? 'Plan' }} - {{ $pricing->term_months }} month(s) - {{ number_format((float) $pricing->final_amount, 2) }} {{ $pricing->currency }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-2">
                    <label>{{ __('Starts At') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input name="starts_at" type="datetime-local" value="{{ old('starts_at', isset($item->starts_at) ? $item->starts_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                    </div>
                </div>

                <div class="mb-2">
                    <label>{{ __('Grace Ends At (Optional)') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input name="grace_ends_at" type="datetime-local" value="{{ old('grace_ends_at', isset($item->grace_ends_at) ? $item->grace_ends_at->format('Y-m-d\TH:i') : '') }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                    </div>
                </div>

                <div class="mb-2">
                    <label>{{ __('Status') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        @php $status = old('status', $item->status ?? 'active'); @endphp
                        <select name="status" class="form-select ltr:rounded-l-none rtl:rounded-r-none">
                            <option value="trialing" {{ $status === 'trialing' ? 'selected' : '' }}>Trialing</option>
                            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="past_due" {{ $status === 'past_due' ? 'selected' : '' }}>Past Due</option>
                            <option value="canceled" {{ $status === 'canceled' ? 'selected' : '' }}>Canceled</option>
                            <option value="expired" {{ $status === 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                </div>

                <div class="mb-2">
                    <label>{{ __('Auto Renew') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        @php $autoRenew = (string) old('auto_renew', isset($item) ? (string) $item->auto_renew : '1'); @endphp
                        <select name="auto_renew" class="form-select ltr:rounded-l-none rtl:rounded-r-none">
                            <option value="1" {{ $autoRenew === '1' ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ $autoRenew === '0' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-secondary">{{ __('Submit') }}</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const planSelect = document.getElementById('plan-select');
            const pricingSelect = document.getElementById('pricing-select');

            function filterPricingOptions() {
                const planId = planSelect.value;
                Array.from(pricingSelect.options).forEach(function (option) {
                    if (!option.value) {
                        option.hidden = false;
                        return;
                    }
                    const optionPlanId = option.getAttribute('data-plan-id');
                    option.hidden = planId !== '' && optionPlanId !== planId;
                });

                const selectedOption = pricingSelect.options[pricingSelect.selectedIndex];
                if (selectedOption && selectedOption.hidden) {
                    pricingSelect.value = '';
                }
            }

            planSelect.addEventListener('change', filterPricingOptions);
            filterPricingOptions();
        });
    </script>
</x-layout.default>
