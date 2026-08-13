@php
    $fieldName = $parentName ? $parentName . '[' . $field->name . ']' : $field->name;
    $isRequired = $field->is_required ? 'required' : '';

    // For nested fields, fieldValue should already be the specific value
    // No need to extract again - it's already extracted by the parent
    $currentValue = $fieldValue;

    // Handle array values for non-array field types
    if (is_array($currentValue) && !in_array($field->field_type, ['repeater', 'repeatable', 'responsive_image', 'checkbox'])) {
        $currentValue = '';
    }
@endphp

<div class="bg-white border border-gray-200 rounded-lg p-4 @if($field->parent_id) ml-4 @endif">
    <div class="mb-3">
        <label class="block text-base font-semibold text-gray-800">
            {{ $field->label }}
            @if($field->is_required)
                <span class="text-red-500 ml-1">*</span>
            @endif
        </label>
        @if($field->field_type === 'responsive_image')
            <p class="text-xs text-gray-500 mt-1">Desktop and Mobile versions</p>
        @endif
    </div>
    <!-- Field Type Rendering -->
    <div>
        @switch($field->field_type)
            @case('text')
                @php
                    $inputName = 'data' . ($parentName ? '[' . $parentName . ']' : '') . '[' . $field->name . ']';
                @endphp
                <input type="text"
                       name="{{ $inputName }}"
                       value="{{ $currentValue }}"
                       placeholder="{{ $field->label }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       {{ $isRequired }}>
                @break

            @case('textarea')
                @php $inputName = 'data' . ($parentName ? '[' . $parentName . ']' : '') . '[' . $field->name . ']'; @endphp
                <textarea name="{{ $inputName }}"
                          rows="3"
                          placeholder="{{ $field->label }}"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          {{ $isRequired }}>{{ $currentValue }}</textarea>
                @break

            @case('number')
                @php $inputName = 'data' . ($parentName ? '[' . $parentName . ']' : '') . '[' . $field->name . ']'; @endphp
                <input type="number"
                       name="{{ $inputName }}"
                       value="{{ $currentValue }}"
                       placeholder="{{ $field->label }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       {{ $isRequired }}>
                @break

            @case('email')
                @php $inputName = 'data' . ($parentName ? '[' . $parentName . ']' : '') . '[' . $field->name . ']'; @endphp
                <input type="email"
                       name="{{ $inputName }}"
                       value="{{ $currentValue }}"
                       placeholder="{{ $field->label }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       {{ $isRequired }}>
                @break

            @case('url')
                @php $inputName = 'data' . ($parentName ? '[' . $parentName . ']' : '') . '[' . $field->name . ']'; @endphp
                <input type="url"
                       name="{{ $inputName }}"
                       value="{{ $currentValue }}"
                       placeholder="https://"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       {{ $isRequired }}>
                @break

            @case('wysiwyg')
                @php $inputName = 'data' . ($parentName ? '[' . $parentName . ']' : '') . '[' . $field->name . ']'; @endphp
                <textarea name="{{ $inputName }}"
                          rows="5"
                          placeholder="{{ $field->label }}"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          {{ $isRequired }}>{{ $currentValue }}</textarea>
                @break

            @case('color')
                @php $inputName = 'data' . ($parentName ? '[' . $parentName . ']' : '') . '[' . $field->name . ']'; @endphp
                <input type="color"
                       name="{{ $inputName }}"
                       value="{{ $currentValue ?: '#000000' }}"
                       class="w-16 h-10 rounded cursor-pointer border-0"
                       {{ $isRequired }}>
                @break

            @case('date')
                @php $inputName = 'data' . ($parentName ? '[' . $parentName . ']' : '') . '[' . $field->name . ']'; @endphp
                <input type="date"
                       name="{{ $inputName }}"
                       value="{{ $currentValue }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       {{ $isRequired }}>
                @break

            @case('datetime')
                @php $inputName = 'data' . ($parentName ? '[' . $parentName . ']' : '') . '[' . $field->name . ']'; @endphp
                <input type="datetime-local"
                       name="{{ $inputName }}"
                       value="{{ $currentValue }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       {{ $isRequired }}>
                @break

            @case('select')
                @php
                    $options = $field->config['options'] ?? [];
                    $inputName = 'data' . ($parentName ? '[' . $parentName . ']' : '') . '[' . $field->name . ']';
                @endphp
                <select name="{{ $inputName }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        {{ $isRequired }}>
                    <option value="">{{__('Select an option')}}</option>
                    @foreach($options as $value => $label)
                        <option value="{{ $value }}" {{ $currentValue == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @break

            @case('checkbox')
                @php
                    $options = $field->config['options'] ?? [];
                    $selectedValues = is_array($currentValue) ? $currentValue : [];
                    $inputName = 'data' . ($parentName ? '[' . $parentName . ']' : '') . '[' . $field->name . ']';
                @endphp
                <div class="space-y-2">
                    @foreach($options as $value => $label)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox"
                                   name="{{ $inputName }}[]"
                                   value="{{ $value }}"
                                   {{ in_array($value, $selectedValues) ? 'checked' : '' }}
                                   {{ $field->is_required ? 'required' : '' }}
                                   class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @break

            @case('radio')
                @php
                    $options = $field->config['options'] ?? [];
                    $inputName = 'data' . ($parentName ? '[' . $parentName . ']' : '') . '[' . $field->name . ']';
                @endphp
                <div class="space-y-2">
                    @foreach($options as $value => $label)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio"
                                   name="{{ $inputName }}"
                                   value="{{ $value }}"
                                   {{ $currentValue == $value ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 focus:ring-2 focus:ring-blue-500"
                                   {{ $isRequired }}>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @break

            @case('image')
                @if($parentName)
                    @php
                        $inputName = 'data[' . $parentName . '][' . $field->name . ']';
                    @endphp
                @else
                    @php
                        $inputName = 'data[' . $field->name . ']';
                    @endphp
                @endif

                <x-common.file-manager-upload
                    name="{{ $inputName }}"
                    :value="$currentValue"
                    label="{{ $field->label }}"
                    :required="$field->is_required"
                    width="400"
                    height="300"
                />
                @break

            @case('file')
                @php $inputName = 'data' . ($parentName ? '[' . $parentName . ']' : '') . '[' . $field->name . ']'; @endphp
                <input type="text"
                       name="{{ $inputName }}"
                       value="{{ $currentValue }}"
                       placeholder="https://example.com/file.pdf"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       {{ $isRequired }}>
                @break

            @case('responsive_image')
                @php
                    $imageData = is_array($currentValue) ? $currentValue : ['mobile' => '', 'desktop' => ''];

                    // Build proper input names for nested responsive images
                    if ($parentName) {
                        $desktopInputName = 'data[' . $parentName . '][' . $field->name . '][desktop]';
                        $mobileInputName = 'data[' . $parentName . '][' . $field->name . '][mobile]';
                    } else {
                        $desktopInputName = 'data[' . $field->name . '][desktop]';
                        $mobileInputName = 'data[' . $field->name . '][mobile]';
                    }
                @endphp
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Desktop Image</label>
                        <x-common.file-manager-upload
                            name="{{ $desktopInputName }}"
                            :value="$imageData['desktop'] ?? ''"
                            label="Desktop"
                            :required="$field->is_required"
                            width="400"
                            height="300"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mobile Image</label>
                        <x-common.file-manager-upload
                            name="{{ $mobileInputName }}"
                            :value="$imageData['mobile'] ?? ''"
                            label="Mobile"
                            :required="$field->is_required"
                            width="400"
                            height="300"
                        />
                    </div>
                </div>
                @break

            @case('repeater')
                @php
                    $repeaterItems = is_array($currentValue) ? $currentValue : [];
                    $childFields = $field->children;
                @endphp

                @if($childFields->isNotEmpty())
                    <div x-data="{
                        items: {{ json_encode($repeaterItems) }},
                        newItemCounter: {{ count($repeaterItems) }},
                        baseFieldName: '{{ $parentName ? $parentName . '[' . $field->name . ']' : $field->name }}',

                        addItem() {
                            this.items.push({});
                            this.newItemCounter++;
                        },

                        removeItem(index) {
                            if (confirm('Are you sure you want to remove this item?')) {
                                this.items.splice(index, 1);
                            }
                        }
                    }" class="space-y-4">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg relative">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="font-semibold text-gray-700">
                                        <span x-text="'Item ' + (index + 1)"></span>
                                    </h4>
                                    <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                        🗑️ Remove
                                    </button>
                                </div>

                                <div class="space-y-3">
                                    @foreach($childFields as $childField)
                                        @php
                                            $childFieldKey = $childField->name;
                                        @endphp
                                        @include('admin.section-translation.partials.repeater-child-field', [
                                            'field' => $childField,
                                            'parentName' => $parentName,
                                            'repeaterFieldName' => $field->name,
                                            'language' => $language
                                        ])
                                    @endforeach
                                </div>
                            </div>
                        </template>

                        {{-- Add More Button --}}
                        <div class="text-center">
                            <button type="button" @click="addItem()" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium inline-flex items-center gap-2">
                                ➕ Add More Items
                            </button>
                        </div>
                    </div>
                @else
                    <p class="text-gray-500 text-sm">No fields configured for this repeater.</p>
                @endif
                @break

            @case('repeatable')
                @php
                    $repeaterItems = is_array($currentValue) ? $currentValue : [];
                    $childFields = $field->children;
                    $repeaterId = 'repeater_' . uniqid();
                @endphp

                @if($childFields->isNotEmpty())
                    <div x-data="{
                        items: {{ json_encode($repeaterItems) }},
                        newItemCounter: {{ count($repeaterItems) }},
                        baseFieldName: '{{ $parentName ? $parentName . '[' . $field->name . ']' : $field->name }}',

                        addItem() {
                            this.items.push({});
                            this.newItemCounter++;
                        },

                        removeItem(index) {
                            if (confirm('Are you sure you want to remove this item?')) {
                                this.items.splice(index, 1);
                            }
                        }
                    }" class="space-y-4">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg relative">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="font-semibold text-gray-700">
                                        <span x-text="'Item ' + (index + 1)"></span>
                                    </h4>
                                    <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                        🗑️ Remove
                                    </button>
                                </div>

                                <div class="space-y-3">
                                    @foreach($childFields as $childField)
                                        @php
                                            $childFieldKey = $childField->name;
                                        @endphp
                                        @include('admin.section-translation.partials.repeater-child-field', [
                                            'field' => $childField,
                                            'parentName' => $parentName,
                                            'repeaterFieldName' => $field->name,
                                            'language' => $language
                                        ])
                                    @endforeach
                                </div>
                            </div>
                        </template>

                        {{-- Add More Button --}}
                        <div class="text-center">
                            <button type="button" @click="addItem()" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium inline-flex items-center gap-2">
                                ➕ Add More Items
                            </button>
                        </div>
                    </div>
                @else
                    <p class="text-gray-500 text-sm">No fields configured for this repeater.</p>
                @endif
                @break

            @default
                @php $inputName = 'data' . ($parentName ? '[' . $parentName . ']' : '') . '[' . $field->name . ']'; @endphp
                <input type="text"
                       name="{{ $inputName }}"
                       value="{{ $currentValue }}"
                       placeholder="{{ $field->label }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       {{ $isRequired }}>
        @endswitch
    </div>

    {{-- Render child fields if any (except for repeaters/repeatables which handle their own children) --}}
    @if($field->children->isNotEmpty() && !in_array($field->field_type, ['repeater', 'repeatable']))
        <div class="mt-4 space-y-3">
            @foreach($field->children as $childField)
                @php
                    $childValue = is_array($currentValue) ? ($currentValue[$childField->name] ?? '') : '';
                    $childParentName = $parentName ? $parentName . '[' . $field->name . ']' : $field->name;
                @endphp
                @include('admin.section-translation.partials.field-renderer', [
                    'field' => $childField,
                    'fieldValue' => $childValue,
                    'language' => $language,
                    'parentName' => $childParentName
                ])
            @endforeach
        </div>
    @endif
</div>
