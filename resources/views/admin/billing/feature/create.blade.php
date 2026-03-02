<x-layout.default>
    @section('title', $pageTitle)

    @php
        $currentItem = $item ?? null;
        $translationMap = $currentItem
            ? $currentItem->translations->keyBy('language_id')
            : collect();
        $defaultLanguageId = $default_language?->id;
    @endphp

    <div class="panel mt-6" x-data="{ tab: '{{ $defaultLanguageId }}' }">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">{{ $pageTitle }}</h1>

        <form method="POST" action="{{ $currentItem ? route('feature.update', $currentItem->id) : route('feature.store') }}">
            @csrf
            @if($currentItem)
                @method('PUT')
                <input type="hidden" name="edit_id" value="{{ $currentItem->id }}">
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="mb-2">
                    <label>{{ __('Feature Key') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input name="key" type="text" value="{{ old('key', $currentItem?->key ?? '') }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" placeholder="vehicle.max_count" required />
                    </div>
                </div>

                <div class="mb-2">
                    <label>{{ __('Value Type') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <select name="value_type" class="form-select ltr:rounded-l-none rtl:rounded-r-none">
                            @php $selectedType = old('value_type', $currentItem?->value_type ?? 'boolean'); @endphp
                            <option value="boolean" {{ $selectedType === 'boolean' ? 'selected' : '' }}>Boolean</option>
                            <option value="integer" {{ $selectedType === 'integer' ? 'selected' : '' }}>Integer</option>
                            <option value="decimal" {{ $selectedType === 'decimal' ? 'selected' : '' }}>Decimal</option>
                            <option value="string" {{ $selectedType === 'string' ? 'selected' : '' }}>String</option>
                            <option value="json" {{ $selectedType === 'json' ? 'selected' : '' }}>JSON</option>
                        </select>
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

            <h5 class="text-xl font-semi-bold text-gray-600 dark:text-gray-100 mt-8 mb-4">{{ __('Translations') }}</h5>

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
                            $descriptionOld = old("translations.{$language->id}.description", $existing->description ?? ((int) $language->is_default === 1 ? ($currentItem?->description ?? '') : ''));
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

                                <div class="mb-2 md:col-span-2">
                                    <label>{{ __('Description') }}</label>
                                    <textarea name="translations[{{ $language->id }}][description]" class="form-textarea min-h-[90px]">{{ $descriptionOld }}</textarea>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-secondary">{{ __('Submit') }}</button>
            </div>
        </form>
    </div>
</x-layout.default>
