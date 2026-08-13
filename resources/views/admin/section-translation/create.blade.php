<x-layout.default>
@section('title', $pageTitle)

@php
    $currentItem = $item ?? null;
    $availableLanguages = $languages->reject(function($language) use ($existingLanguageIds) {
        return in_array($language->id, $existingLanguageIds);
    });
    $defaultLanguage = $languages->firstWhere('is_default', 1);
@endphp

<div class="panel mt-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">
        {{ $pageTitle }}
    </h1>

    <div class="mb-6">
        <p class="text-gray-600">{{ __('Page') }}: {{ $page->name }}</p>
        <p class="text-gray-600">{{ __('Component') }}: {{ $section->component->title }}</p>
    </div>

    @if($availableLanguages->isNotEmpty())
        <div x-data="{
            selectedLanguage: '{{ old('language_id', $availableLanguages->first()->id) }}',
            copyFromDefault: false,
            isCopying: false,
            copySuccess: false,
            copyError: '',
            async copyFromDefaultLanguage() {
                this.isCopying = true;
                this.copySuccess = false;
                this.copyError = '';

                try {
                    const response = await fetch('{{ route('pages.sections.translations.default-language-content', ['pageId' => $page->id, 'sectionId' => $section->id]) }}');
                    const result = await response.json();

                    if (result.success && result.data) {
                        // Show success message
                        this.copySuccess = true;
                        this.copyError = '';

                        // Create a notification element
                        const notification = document.createElement('div');
                        notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-50 flex items-center gap-3 transform transition-all duration-300';
                        notification.innerHTML = `
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <div>
                                <p class="font-semibold">${result.data.language.name} content copied successfully!</p>
                                <p class="text-sm opacity-90">${result.data.content.length} fields ready to edit</p>
                            </div>
                        `;
                        document.body.appendChild(notification);

                        // Remove notification after 3 seconds
                        setTimeout(() => {
                            notification.style.opacity = '0';
                            setTimeout(() => notification.remove(), 300);
                        }, 3000);

                        // Store the copied data in localStorage for the next step
                        localStorage.setItem('copiedTranslationData', JSON.stringify(result.data.content));
                        localStorage.setItem('copiedFromLanguage', result.data.language.name);

                    } else {
                        this.copyError = result.message || 'Failed to copy content';
                        const errorNotification = document.createElement('div');
                        errorNotification.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                        errorNotification.textContent = this.copyError;
                        document.body.appendChild(errorNotification);
                        setTimeout(() => errorNotification.remove(), 3000);
                    }
                } catch (error) {
                    this.copyError = 'Network error occurred';
                    const errorNotification = document.createElement('div');
                    errorNotification.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                    errorNotification.textContent = this.copyError;
                    document.body.appendChild(errorNotification);
                    setTimeout(() => errorNotification.remove(), 3000);
                } finally {
                    this.isCopying = false;
                }
            }
        }">
            <form method="POST" action="{{ route('pages.sections.translations.store', ['pageId' => $page->id, 'sectionId' => $section->id]) }}" enctype="multipart/form-data">
                @csrf

                <!-- Language Selection with Tabs -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Select Language to Translate') }} <span class="text-red-500">*</span></label>

                    <div class="panel p-0 overflow-hidden border border-gray-200">
                        <div class="border-b border-gray-200 bg-gray-50">
                            <ul class="flex flex-wrap text-sm font-medium text-gray-600">
                                @foreach($availableLanguages as $language)
                                    <li>
                                        <button
                                            type="button"
                                            class="px-4 py-3 border-r border-gray-200 transition"
                                            :class="selectedLanguage == '{{ $language->id }}' ? 'bg-white text-primary font-semibold' : 'hover:bg-gray-100'"
                                            @click="selectedLanguage = '{{ $language->id }}'; $el.closest('form').querySelector('input[name=\'language_id\']').value = '{{ $language->id }}'"
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
                            @foreach($availableLanguages as $language)
                                <div x-show="selectedLanguage == '{{ $language->id }}'" x-cloak>
                                    <div class="flex items-center justify-between mb-4">
                                        <div>
                                            <h4 class="font-semibold text-gray-800">{{ $language->name }} Translation</h4>
                                            <p class="text-sm text-gray-600">
                                                @if((int) $language->is_default === 1)
                                                    <span class="text-blue-600">{{__('Default Language - Primary Translation')}}</span>
                                                @else
                                                    <span class="text-gray-500">{{__('Additional Language Translation')}}</span>
                                                @endif
                                            </p>
                                        </div>

                                        @if($defaultLanguage && $language->id != $defaultLanguage->id)
                                            <div class="flex items-center gap-2">
                                                @if(isset($defaultTranslation) && $defaultTranslation && !empty($defaultTranslation->data))
                                                    <button type="button"
                                                            class="inline-flex items-center px-3 py-1.5 text-sm bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors border border-blue-200"
                                                            :disabled="isCopying"
                                                            @click="copyFromDefaultLanguage()">
                                                        <template x-if="!isCopying">
                                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                            </svg>
                                                        </template>
                                                        <template x-if="isCopying">
                                                            <svg class="animate-spin w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                            </svg>
                                                        </template>
                                                        <span x-text="isCopying ? 'Copying...' : 'Copy from ' + '{{ $defaultLanguage->name }}'"></span>
                                                    </button>
                                                @else
                                                    <span class="text-xs text-gray-400 italic">{{__('No default content available')}}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Copy Success Message -->
                                    <div x-show="copySuccess" x-transition class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                                        <div class="flex items-center text-green-700">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span class="text-sm">{{__('Content copied! After creating this translation, you can edit the fields with the copied data as a starting point.')}}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <input type="hidden" name="language_id" :value="selectedLanguage" required>
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
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-lg hover:from-indigo-700 hover:to-blue-700 transition-all">
                        <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('Create Translation') }}
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900">{{__('All languages have translations')}}</h3>
            <p class="text-gray-500 mt-2">{{__('All available languages already have translations for this section. Consider editing existing translations or adding more languages.')}}</p>
            <div class="mt-6 flex justify-center gap-4">
                <a href="{{ route('pages.sections.translations.index', ['pageId' => $page->id, 'sectionId' => $section->id]) }}" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    {{__('View Existing Translations')}}
                </a>
                <a href="{{ route('language.list') }}" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    {{__('Add More Languages')}}
                </a>
            </div>
        </div>
    @endif
</div>
</x-layout.default>