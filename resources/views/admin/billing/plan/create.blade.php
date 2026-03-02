<x-layout.default>
    @section('title', $pageTitle)

    @php
        $currentItem = $item ?? null;
        $translationMap = $currentItem
            ? $currentItem->translations->keyBy('language_id')
            : collect();
        $featureValueMap = $currentItem ? $currentItem->featureValues->keyBy('feature_id') : collect();
        $pricingMap = $currentItem ? $currentItem->pricings->keyBy('term_months') : collect();
        $defaultLanguageId = $default_language?->id;
    @endphp

    <div class="panel mt-6" x-data="{ tab: '{{ $defaultLanguageId }}' }">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">{{ $pageTitle }}</h1>

        <form method="POST" action="{{ $currentItem ? route('plan.update', $currentItem->id) : route('plan.store') }}">
            @csrf
            @if($currentItem)
                @method('PUT')
                <input type="hidden" name="edit_id" value="{{ $currentItem->id }}">
            @endif

            <h5 class="text-xl font-semi-bold text-gray-600 dark:text-gray-100 mb-4">{{ __('Plan Information') }}</h5>

            <div class="panel p-0 overflow-hidden border border-gray-200">
                <div class="border-b border-gray-200 bg-gray-50">
                    <ul class="flex flex-wrap text-sm font-medium text-gray-600">
                        @foreach($languages as $language)
                            <li>
                                <button
                                    type="button"
                                    class="px-4 py-3 border-r border-gray-200 transition"
                                    :class="tab == '{{ $language->id }}' ? 'bg-white text-primary font-semibold' : 'hover:bg-gray-100'"
                                    @click="tab = '{{ $language->id }}'"
                                >
                                    {{ $language->name }} ({{ strtoupper($language->code) }})
                                    @if((int) $language->is_default === 1)
                                        <span class="text-danger">*</span>
                                    @endif
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="p-4">
                    @foreach($languages as $language)
                        @php
                            $existing = $translationMap->get($language->id);
                            $nameOld = old("translations.{$language->id}.name", $existing->name ?? ((int) $language->is_default === 1 ? ($currentItem?->name ?? '') : ''));
                            $subtitleOld = old("translations.{$language->id}.subtitle", $existing->subtitle ?? ((int) $language->is_default === 1 ? ($currentItem?->subtitle ?? '') : ''));
                        @endphp

                        <div x-show="tab == '{{ $language->id }}'" x-cloak>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="mb-2">
                                    <label>
                                        {{ __('Name') }}
                                        @if((int) $language->is_default === 1)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                    <div class="flex">
                                        {!! defaultInputIcon() !!}
                                        <input
                                            name="translations[{{ $language->id }}][name]"
                                            type="text"
                                            value="{{ $nameOld }}"
                                            class="form-input ltr:rounded-l-none rtl:rounded-r-none"
                                            {{ (int) $language->is_default === 1 ? 'required' : '' }}
                                        />
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <label>{{ __('Sub title') }}</label>
                                    <div class="flex">
                                        {!! defaultInputIcon() !!}
                                        <input
                                            name="translations[{{ $language->id }}][subtitle]"
                                            type="text"
                                            value="{{ $subtitleOld }}"
                                            class="form-input ltr:rounded-l-none rtl:rounded-r-none"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
                <div class="mb-2">
                    <label>{{ __('Slug') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input name="slug" type="text" value="{{ old('slug', $currentItem?->slug ?? '') }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                    </div>
                </div>
                <div class="mb-2">
                    <label>{{ __('Sort Order') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input name="sort_order" type="number" min="0" value="{{ old('sort_order', $currentItem?->sort_order ?? 0) }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                    </div>
                </div>
                <div class="mb-2">
                    <label>{{ __('Status') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        @php $status = (string) old('is_active', $currentItem ? (string) $currentItem->is_active : '1'); @endphp
                        <select name="is_active" class="form-select ltr:rounded-l-none rtl:rounded-r-none">
                            <option value="1" {{ $status === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ $status === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <h5 class="text-xl font-semi-bold text-gray-600 dark:text-gray-100 mt-8 mb-4">{{ __('Plan Features') }}</h5>
            <div class="overflow-x-auto border rounded-lg">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left">{{ __('Assign') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Feature') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Key') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Type') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Value') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($features as $feature)
                            @php
                                $existing = $featureValueMap->get($feature->id);
                                $assignedOld = old("feature_assign.{$feature->id}");
                                $isAssigned = $assignedOld !== null ? (string) $assignedOld === '1' : (bool) $existing;
                            @endphp
                            <tr class="border-t">
                                <td class="px-3 py-2 align-top">
                                    <input type="checkbox" name="feature_assign[{{ $feature->id }}]" value="1" {{ $isAssigned ? 'checked' : '' }} />
                                </td>
                                <td class="px-3 py-2 align-top">{{ $feature->name }}</td>
                                <td class="px-3 py-2 align-top">{{ $feature->key }}</td>
                                <td class="px-3 py-2 align-top">{{ ucfirst($feature->value_type) }}</td>
                                <td class="px-3 py-2 align-top">
                                    @if($feature->value_type === 'boolean')
                                        @php
                                            $boolValue = old("feature_values.{$feature->id}.value_bool");
                                            if ($boolValue === null) {
                                                $boolValue = $existing ? ((int) ($existing->value_bool ? 1 : 0)) : 1;
                                            }
                                        @endphp
                                        <select name="feature_values[{{ $feature->id }}][value_bool]" class="form-select">
                                            <option value="1" {{ (string) $boolValue === '1' ? 'selected' : '' }}>Yes</option>
                                            <option value="0" {{ (string) $boolValue === '0' ? 'selected' : '' }}>No</option>
                                        </select>
                                    @elseif($feature->value_type === 'integer')
                                        <input type="number" class="form-input" name="feature_values[{{ $feature->id }}][value_int]" value="{{ old("feature_values.{$feature->id}.value_int", $existing->value_int ?? '') }}" />
                                    @elseif($feature->value_type === 'decimal')
                                        <input type="number" step="0.01" class="form-input" name="feature_values[{{ $feature->id }}][value_decimal]" value="{{ old("feature_values.{$feature->id}.value_decimal", $existing->value_decimal ?? '') }}" />
                                    @elseif($feature->value_type === 'json')
                                        <textarea class="form-textarea min-h-[70px]" name="feature_values[{{ $feature->id }}][value_json]">{{ old("feature_values.{$feature->id}.value_json", isset($existing->value_json) ? json_encode($existing->value_json) : '') }}</textarea>
                                    @else
                                        <input type="text" class="form-input" name="feature_values[{{ $feature->id }}][value_text]" value="{{ old("feature_values.{$feature->id}.value_text", $existing->value_text ?? '') }}" />
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <h5 class="text-xl font-semi-bold text-gray-600 dark:text-gray-100 mt-8 mb-4">{{ __('Pricing Terms') }}</h5>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @foreach($pricing_terms as $term)
                    @php
                        $existingPricing = $pricingMap->get($term);
                        $isActive = old("pricings.{$term}.is_active");
                        if ($isActive === null) {
                            $isActive = $existingPricing ? (string) $existingPricing->is_active : ($term === 1 ? '1' : '0');
                        }
                    @endphp
                    <div class="border rounded-lg p-4">
                        <h6 class="font-semibold mb-2">{{ $term }} {{ __('Month Term') }}</h6>
                        <input type="hidden" name="pricings[{{ $term }}][term_months]" value="{{ $term }}">
                        <div class="mb-2">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="pricings[{{ $term }}][is_active]" value="1" {{ (string) $isActive === '1' ? 'checked' : '' }}>
                                <span>{{ __('Enable this term') }}</span>
                            </label>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <div>
                                <label>{{ __('Base Amount') }}</label>
                                <input type="number" step="0.01" min="0" class="form-input" name="pricings[{{ $term }}][base_amount]" value="{{ old("pricings.{$term}.base_amount", $existingPricing->base_amount ?? 0) }}">
                            </div>
                            <div>
                                <label>{{ __('Currency') }}</label>
                                <input type="text" class="form-input" name="pricings[{{ $term }}][currency]" value="{{ old("pricings.{$term}.currency", $existingPricing->currency ?? 'BDT') }}">
                            </div>
                            <div>
                                <label>{{ __('Discount Type') }}</label>
                                @php $discountType = old("pricings.{$term}.discount_type", $existingPricing->discount_type ?? 'percent'); @endphp
                                <select class="form-select" name="pricings[{{ $term }}][discount_type]">
                                    <option value="percent" {{ $discountType === 'percent' ? 'selected' : '' }}>Percent</option>
                                    <option value="fixed" {{ $discountType === 'fixed' ? 'selected' : '' }}>Fixed</option>
                                </select>
                            </div>
                            <div>
                                <label>{{ __('Discount Value') }}</label>
                                <input type="number" step="0.01" min="0" class="form-input" name="pricings[{{ $term }}][discount_value]" value="{{ old("pricings.{$term}.discount_value", $existingPricing->discount_value ?? 0) }}">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-5">
                <button type="submit" class="btn btn-secondary">{{ __('Submit') }}</button>
            </div>
        </form>
    </div>
</x-layout.default>
