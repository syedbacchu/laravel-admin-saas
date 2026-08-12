<x-layout.default>
    @section('title', $pageTitle)

    <div class="panel mt-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                {{ $pageTitle }}
            </h1>
            <a href="{{ route('component.fields', $component->id) }}"
                class="text-sm text-blue-600 hover:text-blue-800">
                {{__('Back to Fields')}} → {{ $component->name }}
            </a>
        </div>

        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <!-- Field Header -->
            <div class="bg-gradient-to-r from-green-600 to-teal-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white">{{ $field->label }}</h2>
                <p class="text-green-200 text-sm mt-1">{{ __('Field Name:') }} {{ $field->name }}</p>
            </div>

            <!-- Field Details -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Field Type -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ __('Field Type') }}</h3>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            {{ $field_types[$field->field_type] ?? $field->field_type }}
                        </span>
                    </div>

                    <!-- Parent -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ __('Parent Field') }}</h3>
                        @if($field->parent)
                        <p class="text-gray-600">
                            {{ $field->parent->label }}
                            <small class="text-gray-500">({{ $field->parent->name }})</small>
                        </p>
                        @else
                        <p class="text-gray-500">{{ __('None (root level)') }}</p>
                        @endif
                    </div>

                    <!-- Required -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ __('Required') }}</h3>
                        <span class="inline-flex items-center">
                            @if($field->is_required)
                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="ml-2 text-green-700">{{ __('Yes') }}</span>
                            @else
                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="ml-2 text-gray-500">{{ __('No') }}</span>
                            @endif
                        </span>
                    </div>

                    <!-- Translatable -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ __('Translatable') }}</h3>
                        <span class="inline-flex items-center">
                            @if($field->is_translatable)
                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="ml-2 text-green-700">{{ __('Yes') }}</span>
                            @else
                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="ml-2 text-gray-500">{{ __('No') }}</span>
                            @endif
                        </span>
                    </div>

                    <!-- Sort Order -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ __('Sort Order') }}</h3>
                        <p class="text-gray-600">{{ $field->sort_order }}</p>
                    </div>

                    <!-- Children Count -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ __('Child Fields') }}</h3>
                        <p class="text-gray-600">{{ $field->children->count() }} {{ __('child field(s)') }}</p>
                    </div>
                </div>

                <!-- Configuration -->
                @if($field->config)
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ __('Configuration') }}</h3>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <pre class="text-sm text-gray-700">{{ json_encode($field->config, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
                @endif

                <!-- Child Fields Preview -->
                @if($field->children->count() > 0)
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ __('Child Fields') }}</h3>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <ul class="space-y-2">
                            @foreach($field->children as $child)
                            <li class="flex items-start">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs font-bold mr-3 mt-0.5">
                                    {{ $loop->iteration }}
                                </span>
                                <div>
                                    <span class="font-medium text-gray-800">{{ $child->label }}</span>
                                    <span class="text-gray-500 text-sm ml-2">({{ $child->field_type }})</span>
                                    @if($child->is_required)
                                    <span class="text-red-500 text-xs ml-1">*</span>
                                    @endif
                                    @if($child->is_translatable)
                                    <span class="text-blue-500 text-xs ml-1">🌐</span>
                                    @endif
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                <!-- Metadata -->
                <div class="border-t border-gray-200 pt-6 mt-6">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">{{ __('Created:') }}</span>
                            <span class="text-gray-700 ml-2">{{ $field->created_at ? $field->created_at->format('M d, Y H:i') : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">{{ __('Updated:') }}</span>
                            <span class="text-gray-700 ml-2">{{ $field->updated_at ? $field->updated_at->format('M d, Y H:i') : '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end mt-6 pt-6 border-t border-gray-200">
                    <a href="{{ route('component.field.edit', ['component' => $component->id, 'field' => $field->id]) }}"
                        class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-lg hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        {{ __('Edit Field') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layout.default>