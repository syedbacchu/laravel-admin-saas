<x-layout.default>
@section('title', $pageTitle)

@php
    $allLanguages = \App\Models\Language::active()->get();
    $translatedLanguageIds = $translations->pluck('language_id')->toArray();
    $missingLanguages = $allLanguages->reject(function($language) use ($translatedLanguageIds) {
        return in_array($language->id, $translatedLanguageIds);
    });
    $totalLanguages = $allLanguages->count();
    $completionPercentage = $totalLanguages > 0 ? round(($translations->count() / $totalLanguages) * 100) : 0;
@endphp

<div class="mt-8 bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
    <!-- Header with Translation Status -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
        <div class="flex-1">
            <div class="flex items-center gap-4">
                <div>
                    <h5 class="text-2xl font-bold text-gray-800">{{ $pageTitle ?? __('Translations') }}</h5>
                    <p class="text-gray-600 mt-1">{{ __('Component') }}: {{ $section->component->title }}</p>
                    <p class="text-gray-600">{{ __('Page') }}: {{ $page->name }}</p>
                </div>

                <!-- Translation Status Badge -->
                <div class="flex-shrink-0">
                    @if($completionPercentage === 100)
                        <div class="px-4 py-2 bg-green-100 text-green-800 rounded-lg text-center">
                            <div class="text-2xl font-bold">{{ $completionPercentage }}%</div>
                            <div class="text-xs">{{__('Complete')}}</div>
                        </div>
                    @elseif($completionPercentage >= 50)
                        <div class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded-lg text-center">
                            <div class="text-2xl font-bold">{{ $completionPercentage }}%</div>
                            <div class="text-xs">{{__('In Progress')}}</div>
                        </div>
                    @else
                        <div class="px-4 py-2 bg-red-100 text-red-800 rounded-lg text-center">
                            <div class="text-2xl font-bold">{{ $completionPercentage }}%</div>
                            <div class="text-xs">{{__('Just Started')}}</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mt-4">
                <div class="flex items-center justify-between text-sm mb-1">
                    <span class="text-gray-600">{{__('Translation Progress')}}</span>
                    <span class="font-semibold">{{ $translations->count() }}/{{ $totalLanguages }} {{__('languages')}}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="h-2 rounded-full @if($completionPercentage === 100) bg-green-500 @elseif($completionPercentage >= 50) bg-yellow-500 @else bg-red-500 @endif"
                         style="width: {{ $completionPercentage }}%"></div>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            @if($missingLanguages->isNotEmpty())
                <a href="{{ route('pages.sections.translations.create', ['pageId' => $page->id, 'sectionId' => $section->id]) }}"
                   class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-medium rounded-lg shadow-md hover:shadow-lg hover:from-indigo-700 hover:to-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4v16m8-8H4" />
                    </svg>
                    {{__('Add Missing')}} ({{ $missingLanguages->count() }})
                </a>
            @endif
            <a href="{{ route('pages.sections.index', ['pageId' => $page->id]) }}"
               class="inline-flex items-center px-5 py-2.5 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{__('Back to Sections')}}
            </a>
        </div>
    </div>

    <!-- Missing Languages Alert -->
    @if($missingLanguages->isNotEmpty())
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-amber-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="flex-1">
                    <h4 class="font-semibold text-amber-800">{{__('Missing Translations')}}</h4>
                    <p class="text-sm text-amber-700 mt-1">
                        {{__('The following languages are missing translations')}}:
                        <strong>{{ $missingLanguages->pluck('name')->implode(', ') }}</strong>
                    </p>
                    <a href="{{ route('pages.sections.translations.create', ['pageId' => $page->id, 'sectionId' => $section->id]) }}"
                       class="text-sm text-amber-800 underline mt-2 inline-block hover:text-amber-900">
                        {{__('Add missing translations now')}} →
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Enhanced Table -->
    @if($translations->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 rounded-xl text-sm text-gray-700">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide">{{__('Language')}}</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide">{{__('Content Status')}}</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide">{{__('Content Preview')}}</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide">{{__('Last Updated')}}</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide">{{__('Actions')}}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($translations as $translation)
                        @php
                            $contentCount = is_array($translation->data) ? count($translation->data) : 0;
                            $hasContent = $contentCount > 0;
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold @if((int) $translation->language->is_default === 1) bg-green-100 text-green-800 @else bg-blue-100 text-blue-800 @endif">
                                        {{ $translation->language->name }}
                                    </span>
                                    @if((int) $translation->language->is_default === 1)
                                        <span class="text-xs text-green-600 font-semibold">{{__('Default')}}</span>
                                    @endif
                                    <small class="text-gray-500">({{ strtoupper($translation->language->code) }})</small>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($hasContent)
                                    <div class="flex items-center text-green-600">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span class="text-xs font-semibold">{{ $contentCount }} {{__('fields')}}</span>
                                    </div>
                                @else
                                    <div class="flex items-center text-amber-600">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        <span class="text-xs">{{__('Empty')}}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="max-w-xs">
                                    @if($hasContent)
                                        @foreach($translation->data as $key => $value)
                                            @if($loop->iteration <= 2)
                                                @if(is_string($value) && strlen($value) > 0)
                                                    <div class="mb-1">
                                                        <span class="font-medium text-gray-700">{{ $key }}:</span>
                                                        <span class="text-gray-600">
                                                            @if(is_string($value) && strlen($value) > 40)
                                                                {{ substr($value, 0, 40) }}...
                                                            @else
                                                                {{ $value }}
                                                            @endif
                                                        </span>
                                                    </div>
                                                @endif
                                            @endif
                                        @endforeach
                                        @if($contentCount > 2)
                                            <div class="text-xs text-gray-500 italic">{{__('+')}} {{ $contentCount - 2 }} {{__('more fields')}}</div>
                                        @endif
                                    @else
                                        <span class="text-gray-400 italic">{{__('No content')}}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs text-gray-500">
                                    {{ $translation->updated_at ? $translation->updated_at->diffForHumans() : __('Never') }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('pages.sections.translations.edit-content', ['pageId' => $page->id, 'sectionId' => $section->id]) }}?language_id={{ $translation->language_id }}"
                                       class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="{{__('Edit Content')}}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('pages.sections.translations.edit', ['pageId' => $page->id, 'sectionId' => $section->id, 'id' => $translation->id]) }}"
                                       class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="{{__('Edit Settings')}}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </a>
                                    <form action="{{ route('pages.sections.translations.delete', ['pageId' => $page->id, 'sectionId' => $section->id, 'id' => $translation->id]) }}"
                                          method="POST" onsubmit="return confirm('{{__("Are you sure you want to delete this translation?")}}');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="{{__('Delete')}}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-16">
            <div class="flex justify-center mb-6">
                <div class="relative">
                    <div class="absolute inset-0 bg-blue-100 rounded-full opacity-20 animate-ping"></div>
                    <svg class="w-20 h-20 text-blue-600 relative" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                    </svg>
                </div>
            </div>

            <h3 class="text-xl font-bold text-gray-900 mb-2">{{__('Start Translating Your Content')}}</h3>
            <p class="text-gray-500 max-w-md mx-auto mb-6">
                {{__('This section has no translations yet. Create your first translation to make your content available in multiple languages.')}}
            </p>

            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ route('pages.sections.translations.create', ['pageId' => $page->id, 'sectionId' => $section->id]) }}"
                   class="inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-semibold rounded-lg hover:from-indigo-700 hover:to-blue-700 transition-all shadow-lg hover:shadow-xl">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{__('Create First Translation')}}
                </a>
                <a href="{{ route('language.list') }}" class="inline-flex items-center justify-center px-6 py-3 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                    {{__('Configure Languages')}}
                </a>
            </div>

            <div class="mt-8 p-4 bg-blue-50 rounded-lg max-w-md mx-auto">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-left text-sm text-blue-700">
                        <p class="font-semibold mb-1">{{__('Translation Tips')}}</p>
                        <ul class="space-y-1 text-blue-600">
                            <li>• {{__('Start with your default language')}}</li>
                            <li>• {{__('Use the "Copy" feature for similar languages')}}</li>
                            <li>• {{__('Review all required component fields')}}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
</x-layout.default>