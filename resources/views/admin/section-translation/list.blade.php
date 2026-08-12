<x-layout.default>
@section('title', $pageTitle)

<div class="mt-8 bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
        <div>
            <h5 class="text-2xl font-bold text-gray-800">{{ $pageTitle ?? __('Translations') }}</h5>
            <p class="text-gray-600 mt-1">{{ __('Component') }}: {{ $section->component->title }}</p>
            <p class="text-gray-600">{{ __('Page') }}: {{ $page->name }}</p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('pages.sections.translations.create', ['pageId' => $page->id, 'sectionId' => $section->id]) }}"
               class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-medium rounded-lg shadow-md hover:shadow-lg hover:from-indigo-700 hover:to-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4v16m8-8H4" />
                </svg>
                {{__('Add Translation')}}
            </a>
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

    <!-- Table -->
    @if($translations->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 rounded-xl text-sm text-gray-700">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide">{{__('Language')}}</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide">{{__('Content Preview')}}</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide">{{__('Actions')}}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($translations as $translation)
                        <tr>
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">{{ $translation->language->name }}</span>
                                <small class="text-gray-500 ml-2">({{ $translation->language->code }})</small>
                            </td>
                            <td class="px-4 py-3">
                                @foreach($translation->data as $key => $value)
                                    @if(is_string($value) && strlen($value) > 50)
                                        <div class="mb-1"><strong class="text-gray-700">{{ $key }}:</strong> {{ substr($value, 0, 50) }}...</div>
                                    @elseif(is_string($value))
                                        <div class="mb-1"><strong class="text-gray-700">{{ $key }}:</strong> {{ $value }}</div>
                                    @endif
                                @endforeach
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('pages.sections.translations.edit', ['pageId' => $page->id, 'sectionId' => $section->id, 'id' => $translation->id]) }}"
                                       class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="{{__('Edit')}}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('pages.sections.translations.edit-content', ['pageId' => $page->id, 'sectionId' => $section->id]) }}?language_id={{ $translation->language_id }}"
                                       class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="{{__('Edit Content')}}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
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
        <div class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900">{{__('No translations found')}}</h3>
            <p class="text-gray-500 mt-2">{{__('Get started by creating a new translation.')}}</p>
            <a href="{{ route('pages.sections.translations.create', ['pageId' => $page->id, 'sectionId' => $section->id]) }}"
               class="inline-flex items-center px-6 py-3 mt-6 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-medium rounded-lg hover:from-indigo-700 hover:to-blue-700 transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{__('Create Translation')}}
            </a>
        </div>
    @endif
</div>
</x-layout.default>