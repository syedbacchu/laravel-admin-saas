<x-layout.default>
@section('title', $pageTitle)

<div class="panel mt-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">
        {{ $pageTitle }}
    </h1>

    <div>
        <form method="POST" action="{{ route('pages.sections.update', ['pageId' => $page->id, 'id' => $section->id]) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Component Selection -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Component') }} <span class="text-red-500">*</span></label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <select name="component_id" class="form-select w-full" required>
                            <option value="">{{ __('Select a component') }}</option>
                            @foreach($components as $component)
                                <option value="{{ $component->id }}" {{ old('component_id', $section->component_id) == $component->id ? 'selected' : '' }}>
                                    {{ $component->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('component_id')
                        <div class="text-red-500 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Sort Order -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Sort Order') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input type="number" name="sort_order" value="{{ old('sort_order', $section->sort_order) }}"
                            class="form-input w-full" min="1" placeholder="{{ __('Enter sort order') }}">
                    </div>
                    @error('sort_order')
                        <div class="text-red-500 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Visibility Status -->
                <div class="mb-4 md:col-span-2">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Visibility') }}</label>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_visible" value="1" {{ old('is_visible', $section->is_visible) ? 'checked' : '' }}
                            class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label class="ml-2 text-gray-700">{{ __('Visible') }}</label>
                    </div>
                    @error('is_visible')
                        <div class="text-red-500 mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('pages.sections.translations.index', ['pageId' => $page->id, 'sectionId' => $section->id]) }}" class="px-6 py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                    </svg>
                    {{ __('Manage Translations') }}
                </a>
                <a href="{{ route('pages.sections.index', ['pageId' => $page->id]) }}" class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-lg hover:from-indigo-700 hover:to-blue-700 transition-all">
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('Update Section') }}
                </button>
            </div>
        </form>
    </div>
</div>
</x-layout.default>