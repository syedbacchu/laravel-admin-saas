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

        <!-- Component Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600 mr-2" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm text-blue-600">
                    {{__('Adding field to')}} <strong>{{ $component->name }}</strong>
                </span>
            </div>
        </div>

        <div>
            <form method="POST" action="{{ $function_type === 'create'
    ? route('component.field.store', $component->id)
    : route('component.field.update', ['component' => $component->id, 'field' => $item->id]) }}" enctype="multipart/form-data">
                @csrf
                @if($function_type === 'update')
                    @method('PUT')
                    <input type="hidden" name="edit_id" value="{{ $item->id }}">
                @endif

                <!-- Field Name -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Field Name') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input type="text" name="name" value="{{ $item->name ?? old('name') }}"
                            class="form-input w-full" required placeholder="{{ __('e.g., title, description, image') }}">
                    </div>
                    <small class="text-gray-500">{{ __('Used internally (no spaces, special characters)') }}</small>
                </div>

                <!-- Field Label -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Field Label') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input type="text" name="label" value="{{ $item->label ?? old('label') }}"
                            class="form-input w-full" required placeholder="{{ __('e.g., Title, Description, Main Image') }}">
                    </div>
                    <small class="text-gray-500">{{ __('Display name for users') }}</small>
                </div>

                <!-- Field Type -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Field Type') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <select name="field_type" id="fieldType" class="form-select w-full" required>
                            <option value="">{{ __('Select field type') }}</option>
                            @foreach($field_types as $value => $label)
                                <option value="{{ $value }}"
                                    @selected(($item->field_type ?? old('field_type')) == $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Parent Field (for nested fields) -->
                @if($function_type === 'create' || ($function_type === 'update' && $item->parent_id))
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Parent Field') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <select name="parent_id" class="form-select w-full">
                            <option value="">{{ __('No parent (root level)') }}</option>
                            @foreach($parent_fields as $field)
                                @if($field->field_type === 'repeatable')
                                    <option value="{{ $field->id }}"
                                        @selected(($item->parent_id ?? old('parent_id')) == $field->id)>
                                        {{ $field->label }} ({{ $field->name }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <small class="text-gray-500">{{ __('Only for child fields of repeatable fields') }}</small>
                </div>
                @endif

                <!-- Required & Translatable -->
                <div class="flex gap-6 mb-4">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_required" value="1"
                            {{ ($function_type === 'create' ? false : ($item->is_required ?? old('is_required'))) ? 'checked' : '' }}
                            class="form-checkbox h-5 w-5 text-indigo-600">
                        <span class="ml-2 text-gray-700">{{ __('Required Field') }}</span>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_translatable" value="1"
                            {{ ($function_type === 'create' ? false : ($item->is_translatable ?? old('is_translatable'))) ? 'checked' : '' }}
                            class="form-checkbox h-5 w-5 text-indigo-600">
                        <span class="ml-2 text-gray-700">{{ __('Translatable') }}</span>
                    </div>
                </div>

                <!-- Sort Order -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Sort Order') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input type="number" name="sort_order" value="{{ $item->sort_order ?? old('sort_order') ?? 0 }}"
                            class="form-input w-full" placeholder="{{ __('Display order') }}">
                    </div>
                    <small class="text-gray-500">{{ __('Lower numbers appear first') }}</small>
                </div>

                <!-- Field-specific Configuration -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                    <h4 class="font-semibold text-gray-800 mb-3">{{ __('Field Configuration') }}</h4>

                    <!-- Repeatable Options -->
                    <div id="repeatableOptions" class="hidden field-config">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 font-medium mb-1">{{ __('Min Items') }}</label>
                                <input type="number" name="min_items" value="{{ $item->config['min_items'] ?? old('min_items') ?? 1 }}"
                                    class="form-input w-full" placeholder="{{ __('Minimum') }}">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-medium mb-1">{{ __('Max Items') }}</label>
                                <input type="number" name="max_items" value="{{ $item->config['max_items'] ?? old('max_items') }}"
                                    class="form-input w-full" placeholder="{{ __('Maximum (optional)') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Number Options -->
                    <div id="numberOptions" class="hidden field-config">
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-gray-700 font-medium mb-1">{{ __('Min') }}</label>
                                <input type="number" name="min" value="{{ $item->config['min'] ?? old('min') }}"
                                    class="form-input w-full" placeholder="{{ __('Min value') }}">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-medium mb-1">{{ __('Max') }}</label>
                                <input type="number" name="max" value="{{ $item->config['max'] ?? old('max') }}"
                                    class="form-input w-full" placeholder="{{ __('Max value') }}">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-medium mb-1">{{ __('Step') }}</label>
                                <input type="number" name="step" value="{{ $item->config['step'] ?? old('step') ?? 1 }}"
                                    class="form-input w-full" placeholder="{{ __('Step') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Text Options -->
                    <div id="textOptions" class="hidden field-config">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 font-medium mb-1">{{ __('Max Length') }}</label>
                                <input type="number" name="max_length" value="{{ $item->config['max_length'] ?? old('max_length') }}"
                                    class="form-input w-full" placeholder="{{ __('Max characters') }}">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-medium mb-1">{{ __('Default Value') }}</label>
                                <input type="text" name="default" value="{{ $item->config['default'] ?? old('default') }}"
                                    class="form-input w-full" placeholder="{{ __('Default') }}">
                            </div>
                        </div>
                    </div>

                    <!-- File Options -->
                    <div id="fileOptions" class="hidden field-config">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 font-medium mb-1">{{ __('Max Size (MB)') }}</label>
                                <input type="number" name="max_size" value="{{ $item->config['max_size'] ?? old('max_size') }}"
                                    class="form-input w-full" placeholder="{{ __('Size limit') }}">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-medium mb-1">{{ __('Allowed Types') }}</label>
                                <input type="text" name="allowed_types" value="{{ $item->config['allowed_types'] ?? old('allowed_types') }}"
                                    class="form-input w-full" placeholder="{{ __('e.g., jpg,png,pdf') }}">
                            </div>
                        </div>
                    </div>

                    <p class="text-sm text-gray-500" id="noConfigMessage">{{ __('No additional configuration for this field type') }}</p>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end mt-6">
                    <a href="{{ route('component.fields', $component->id) }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 mr-3">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-lg hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        {{ $function_type === 'create' ? __('Create Field') : __('Update Field') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .form-input, .form-textarea, .form-checkbox, .form-select {
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            width: 100%;
        }
        .form-textarea {
            resize: vertical;
        }
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