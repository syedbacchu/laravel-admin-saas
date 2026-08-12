<x-layout.default>
@section('title', $pageTitle)

<div class="panel mt-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">
        {{ $pageTitle }}
    </h1>

    <div class="mb-6">
        <p class="text-gray-600">{{ __('Page') }}: {{ $page->name }}</p>
        <p class="text-gray-600">{{ __('Component') }}: {{ $section->component->title }}</p>
    </div>

    <div>
        <form method="POST" action="{{ route('pages.sections.translations.store', ['pageId' => $page->id, 'sectionId' => $section->id]) }}" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Language Selection -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Language') }} <span class="text-red-500">*</span></label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <select name="language_id" class="form-select w-full" required>
                            <option value="">{{ __('Select a language') }}</option>
                            @foreach($languages as $language)
                                @if(!in_array($language->id, $existingLanguageIds))
                                    <option value="{{ $language->id }}" {{ old('language_id') == $language->id ? 'selected' : '' }}>
                                        {{ $language->name }} ({{ $language->code }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    @error('language_id')
                        <div class="text-red-500 mt-1">{{ $message }}</div>
                    @enderror
                    @if(empty($languages->reject(function($language) use ($existingLanguageIds) {
                        return in_array($language->id, $existingLanguageIds);
                    })))
                        <div class="text-amber-500 mt-1">{{__('All languages have translations. Consider editing existing ones.')}}</div>
                    @endif
                </div>

                <!-- Component Info -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Component Information') }}</label>
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <h4 class="font-semibold text-gray-800">{{ $section->component->title }}</h4>
                        <p class="text-gray-600 text-sm mt-1">{{ $section->component->subtitle }}</p>
                        <p class="text-gray-500 text-sm mt-2">{{__('Fields')}}: {{ $section->component->fields->count() }}</p>
                    </div>
                </div>
            </div>

            <!-- Component Fields Preview -->
            <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <h4 class="text-lg font-semibold text-blue-900 mb-3">{{__('Component Fields Overview')}}</h4>
                <p class="text-blue-700 mb-4">
                    {{__('This section uses the')}} <strong>{{ $section->component->title }}</strong> {{__('component with')}} {{ $section->component->fields->count() }} {{__('fields. After creating the translation, you can edit the content for each field.')}}
                </p>

                @if($section->component->fields->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($section->component->fields->sortBy('sort_order')->take(6) as $field)
                            <div class="p-3 bg-white rounded border border-gray-200">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-800">{{ $field->name }}</span>
                                    @if($field->is_required)
                                        <span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded">{{__('Required')}}</span>
                                    @endif
                                </div>
                                <span class="text-xs text-gray-500">{{ $field->field_type }}</span>
                            </div>
                        @endforeach
                    </div>
                    @if($section->component->fields->count() > 6)
                        <p class="text-blue-600 mt-3 text-sm">{{__('And')}} {{ $section->component->fields->count() - 6 }} {{__('more fields...')}}</p>
                    @endif
                @else
                    <p class="text-blue-600">{{__('This component has no fields configured yet.')}}</p>
                @endif
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('pages.sections.translations.index', ['pageId' => $page->id, 'sectionId' => $section->id]) }}" class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-lg hover:from-indigo-700 hover:to-blue-700 transition-all @if(empty($languages->reject(function($language) use ($existingLanguageIds) {
                    return in_array($language->id, $existingLanguageIds);
                }))) opacity-50 cursor-not-allowed @endif" @if(empty($languages->reject(function($language) use ($existingLanguageIds) {
                    return in_array($language->id, $existingLanguageIds);
                }))) disabled @endif>
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('Create Translation') }}
                </button>
            </div>
        </form>
    </div>
</div>
</x-layout.default>