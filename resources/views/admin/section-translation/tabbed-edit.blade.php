<x-layout.default>
@section('title', $pageTitle)

<style>
    [x-cloak] { display: none !important; }
</style>

<div class="panel mt-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">
        {{ $pageTitle }}
    </h1>

    <div class="mb-6">
        <div class="flex flex-wrap gap-4 text-sm text-gray-600">
            <span><strong>{{ __('Page') }}:</strong> {{ $page->name }}</span>
            <span><strong>{{ __('Component') }}:</strong> {{ $section->component->name }}</span>
        </div>
    </div>

    @if($languages->isNotEmpty())
        <div x-data="{
            selectedLanguage: {{ $defaultLanguage ? $defaultLanguage->id : $languages->first()->id }}
        }">
            <!-- Language Tabs -->
            <div class="panel p-0 overflow-hidden border border-gray-200 mb-6">
                <div class="border-b border-gray-200 bg-gray-50">
                    <ul class="flex flex-wrap text-sm font-medium text-gray-600">
                        @foreach($languages as $language)
                            <li>
                                <button
                                    type="button"
                                    class="px-4 py-3 border-r border-gray-200 transition flex items-center gap-2"
                                    :class="selectedLanguage == {{ $language->id }} ? 'bg-white text-primary font-semibold' : 'hover:bg-gray-100'"
                                    @click="selectedLanguage = {{ $language->id }}"
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

                <!-- Tab Content -->
                <div class="p-6">
                    @foreach($languagesData as $langData)
                        @php
                            $language = $langData['language'];
                        @endphp

                        <div x-show="selectedLanguage == {{ $language->id }}" x-cloak>
                            <div class="mb-4">
                                <h3 class="text-lg font-semibold text-gray-800">
                                    {{ $language->name }}
                                    @if((int) $language->is_default === 1)
                                        <span class="ml-2 text-sm font-normal text-blue-600">{{__('(Default Language)')}}</span>
                                    @endif
                                </h3>
                            </div>

                            <!-- Simple Form for Each Language -->
                            <form method="POST" action="{{ route('pages.sections.translations.update-content', ['pageId' => $page->id, 'sectionId' => $section->id]) }}">
                                @csrf
                                <input type="hidden" name="language_id" value="{{ $language->id }}">

                                <div class="space-y-6">
                                    @foreach($langData['fields'] as $field)
                                        @include('admin.section-translation.partials.field-display', [
                                            'field' => $field,
                                            'language' => $language
                                        ])
                                    @endforeach
                                </div>

                                <!-- Simple Save Button -->
                                <div class="mt-6 flex justify-end gap-3">
                                    <a href="{{ route('pages.sections.index', ['pageId' => $page->id]) }}" class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                                        {{__('Back')}}
                                    </a>
                                    <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                        {{__('Save')}} {{ $language->name }} {{__('Translation')}}
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-12">
            <h3 class="text-lg font-medium text-gray-900">{{__('No Configuration Found')}}</h3>
            <p class="text-gray-500 mt-2">
                @if($languages->isEmpty())
                    {{__('No languages available. Please configure languages first.')}}
                @elseif(empty($languagesData[0]['fields']))
                    {{__('This component has no fields configured yet.')}}
                @else
                    {{__('No data available.')}}
                @endif
            </p>
        </div>
    @endif
</div>
</x-layout.default>
