<x-layout.default>
    @section('title', $pageTitle)

    <div class=" mx-auto">
        <!-- Header -->
        <div class="bg-white shadow-lg rounded-xl overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white">{{ $pageTitle }}</h1>
                        <p class="text-indigo-200 text-sm mt-1">{{ __('Component:') }} {{ $componentModel->name }}</p>
                    </div>
                    <a href="{{ route('component.fields', $componentModel->id) }}"
                        class="inline-flex items-center px-3 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        {{__('Back')}}
                    </a>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <form method="POST" action="{{ $function_type === 'create'
    ? route('component.field.store', $componentModel->id)
    : route('component.field.update', ['component' => $componentModel->id, 'field' => $item->id]) }}" enctype="multipart/form-data">
                @csrf
                @if($function_type === 'update')
                    @method('PUT')
                    <input type="hidden" name="edit_id" value="{{ $item->id }}">
                @endif

                <div class="p-6">
                    <!-- Basic Fields Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <!-- Field Name -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('Field Name') }}
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" name="name" value="{{ $item->name ?? old('name') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                    required placeholder="{{ __('e.g., title, description, image') }}">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                    </svg>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">{{ __('Technical name (no spaces)') }}</p>
                        </div>

                        <!-- Field Label -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('Field Label') }}
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" name="label" value="{{ $item->label ?? old('label') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                    required placeholder="{{ __('e.g., Title, Description, Main Image') }}">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">{{ __('Display name for users') }}</p>
                        </div>

                        <!-- Field Type -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('Field Type') }}
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="field_type" id="fieldType"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all appearance-none bg-white"
                                    required>
                                    <option value="">{{ __('Select field type') }}</option>
                                    @foreach($field_types as $value => $label)
                                        <option value="{{ $value }}"
                                            @selected(($item->field_type ?? old('field_type')) == $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Parent Field -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Parent Field') }}</label>
                            <div class="relative">
                                <select name="parent_id"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all appearance-none bg-white">
                                    <option value="">{{ __('No parent (root level)') }}</option>
                                    @foreach($parent_fields as $field)
                                        @if($field->field_type === 'repeatable')
                                            <option value="{{ $field->id }}"
                                                @selected(($item->parent_id ?? old('parent_id')) == $field->id)">
                                                {{ $field->label }} ({{ $field->name }})
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">{{ __('For child fields of repeatable fields') }}</p>
                        </div>

                        <!-- Sort Order -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('Sort Order') }}
                            </label>
                            <div class="relative">
                                <input type="number" name="sort_order" value="{{ $item->sort_order ?? old('sort_order') ?? 0 }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                    placeholder="{{ __('Display order') }}">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9l14 0m-7-7l7 7" />
                                    </svg>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">{{ __('Lower numbers appear first') }}</p>
                        </div>

                        <!-- Empty cell for grid balance -->
                        <div></div>
                    </div>

                    <!-- Toggle Options -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <!-- Required Toggle -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="flex items-center cursor-pointer">
                                        <div class="relative">
                                            <input type="checkbox" name="is_required" value="1"
                                                {{ ($function_type === 'create' ? false : ($item->is_required ?? old('is_required'))) ? 'checked' : '' }}
                                                class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                        </div>
                                        <div class="ml-3">
                                            <span class="text-sm font-medium text-gray-900">{{ __('Required Field') }}</span>
                                            <p class="text-sm text-gray-500">{{ __('Field must be filled') }}</p>
                                        </div>
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    @if(($function_type === 'create' ? false : ($item->is_required ?? old('is_required'))))
                                        <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Translatable Toggle -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="flex items-center cursor-pointer">
                                        <div class="relative">
                                            <input type="checkbox" name="is_translatable" value="1"
                                                {{ ($function_type === 'create' ? false : ($item->is_translatable ?? old('is_translatable'))) ? 'checked' : '' }}
                                                class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                        </div>
                                        <div class="ml-3">
                                            <span class="text-sm font-medium text-gray-900">{{ __('Translatable') }}</span>
                                            <p class="text-sm text-gray-500">{{ __('Multiple languages') }}</p>
                                        </div>
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    @if(($function_type === 'create' ? false : ($item->is_translatable ?? old('is_translatable'))))
                                        <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Field Configuration -->
                    <div class="bg-gradient-to-r from-gray-50 to-blue-50 border border-gray-200 rounded-xl p-6 mb-8">
                        <div class="flex items-center mb-4">
                            <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <h4 class="text-lg font-semibold text-gray-800">{{ __('Field Configuration') }}</h4>
                        </div>

                        <!-- Repeatable Options -->
                        <div id="repeatableOptions" class="hidden field-config">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Min Items') }}</label>
                                    <input type="number" name="min_items" value="{{ $item->config['min_items'] ?? old('min_items') ?? 1 }}"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                        placeholder="{{ __('Minimum') }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Max Items') }}</label>
                                    <input type="number" name="max_items" value="{{ $item->config['max_items'] ?? old('max_items') }}"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                        placeholder="{{ __('Maximum (optional)') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Number Options -->
                        <div id="numberOptions" class="hidden field-config">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Min Value') }}</label>
                                    <input type="number" name="min" value="{{ $item->config['min'] ?? old('min') }}"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                        placeholder="{{ __('Min') }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Max Value') }}</label>
                                    <input type="number" name="max" value="{{ $item->config['max'] ?? old('max') }}"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                        placeholder="{{ __('Max') }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Step') }}</label>
                                    <input type="number" name="step" value="{{ $item->config['step'] ?? old('step') ?? 1 }}"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                        placeholder="{{ __('Step') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Text Options -->
                        <div id="textOptions" class="hidden field-config">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Max Length') }}</label>
                                    <input type="number" name="max_length" value="{{ $item->config['max_length'] ?? old('max_length') }}"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                        placeholder="{{ __('Max characters') }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Default Value') }}</label>
                                    <input type="text" name="default" value="{{ $item->config['default'] ?? old('default') }}"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                        placeholder="{{ __('Default') }}">
                                </div>
                            </div>
                        </div>

                        <!-- File Options -->
                        <div id="fileOptions" class="hidden field-config">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Max Size (MB)') }}</label>
                                    <input type="number" name="max_size" value="{{ $item->config['max_size'] ?? old('max_size') }}"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                        placeholder="{{ __('Size limit') }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Allowed Types') }}</label>
                                    <input type="text" name="allowed_types" value="{{ $item->config['allowed_types'] ?? old('allowed_types') }}"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                        placeholder="{{ __('e.g., jpg,png,pdf') }}">
                                </div>
                            </div>
                        </div>

                        <div id="noConfigMessage" class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p>{{ __('No additional configuration for this field type') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="bg-gray-50 px-6 py-4 flex items-center justify-end">
                    <a href="{{ route('component.fields', $componentModel->id) }}"
                        class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 mr-4 transition-all">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit"
                        class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-medium rounded-lg hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 shadow-lg transition-all">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ $function_type === 'create' ? __('Create Field') : __('Update Field') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .field-config {
            display: none;
        }
        .field-config:not(.hidden) {
            display: block;
        }
    </style>

    <script>
    $(document).ready(function() {
        // Show/hide field-specific configuration
        $('#fieldType').on('change', function() {
            var fieldType = $(this).val();
            $('.field-config').addClass('hidden');
            $('#noConfigMessage').show();

            if (fieldType === 'repeatable') {
                $('#repeatableOptions').removeClass('hidden');
                $('#noConfigMessage').hide();
            } else if (fieldType === 'number') {
                $('#numberOptions').removeClass('hidden');
                $('#noConfigMessage').hide();
            } else if (fieldType === 'text' || fieldType === 'textarea') {
                $('#textOptions').removeClass('hidden');
                $('#noConfigMessage').hide();
            } else if (fieldType === 'image' || fieldType === 'responsive_image' || fieldType === 'file' || fieldType === 'video') {
                $('#fileOptions').removeClass('hidden');
                $('#noConfigMessage').hide();
            }
        });

        // Initialize based on current value
        if ($('#fieldType').val()) {
            $('#fieldType').trigger('change');
        }
    });
    </script>
</x-layout.default>
