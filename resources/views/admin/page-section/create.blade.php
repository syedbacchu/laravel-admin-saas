<x-layout.default>
@section('title', $pageTitle)

<div class="panel mt-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">
        {{ $pageTitle }}
    </h1>

    <div>
        <form method="POST" action="{{ route('pages.sections.store', ['pageId' => $page->id]) }}" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Component Selection -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Component') }} <span class="text-red-500">*</span></label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <select name="component_id" class="form-select w-full" required>
                            <option value="">{{ __('Select a component') }}</option>
                            @foreach($components as $component)
                                <option value="{{ $component->id }}" {{ old('component_id') == $component->id ? 'selected' : '' }}>
                                    {{ $component->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('component_id')
                        <div class="text-red-500 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Visibility Status -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Visibility') }}</label>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_visible" value="1" {{ old('is_visible', '1') ? 'checked' : '' }}
                            class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label class="ml-2 text-gray-700">{{ __('Visible') }}</label>
                    </div>
                    @error('is_visible')
                        <div class="text-red-500 mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('pages.sections.index', ['pageId' => $page->id]) }}" class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-lg hover:from-indigo-700 hover:to-blue-700 transition-all">
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('Add Section') }}
                </button>
            </div>
        </form>
    </div>
</div>
</x-layout.default>