{{-- Clean field display - no logic, just presentation --}}
@php
    $isRequired = $field['is_required'] ? 'required' : '';
    $inputName = 'data[' . $field['input_name'] . ']';
@endphp

<div class="bg-white border border-gray-200 rounded-lg p-4">
    <div class="mb-3">
        <label class="block text-base font-semibold text-gray-800">
            {{ $field['label'] }}
            @if($field['is_required'])
                <span class="text-red-500 ml-1">*</span>
            @endif
        </label>
        @if($field['field_type'] === 'responsive_image')
            <p class="text-xs text-gray-500 mt-1">Desktop and Mobile versions</p>
        @endif
    </div>

    <div>
        @switch($field['field_type'])
            @case('text')
                <input type="text"
                       name="{{ $inputName }}"
                       value="{{ $field['value'] ?? '' }}"
                       placeholder="{{ $field['label'] }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       {{ $isRequired }}>
                @break

            @case('textarea')
                <textarea name="{{ $inputName }}"
                          rows="3"
                          placeholder="{{ $field['label'] }}"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          {{ $isRequired }}>{{ $field['value'] ?? '' }}</textarea>
                @break

            @case('number')
                <input type="number"
                       name="{{ $inputName }}"
                       value="{{ $field['value'] ?? '' }}"
                       placeholder="{{ $field['label'] }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       {{ $isRequired }}>
                @break

            @case('email')
                <input type="email"
                       name="{{ $inputName }}"
                       value="{{ $field['value'] ?? '' }}"
                       placeholder="{{ $field['label'] }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       {{ $isRequired }}>
                @break

            @case('url')
                <input type="url"
                       name="{{ $inputName }}"
                       value="{{ $field['value'] ?? '' }}"
                       placeholder="https://"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       {{ $isRequired }}>
                @break

            @case('wysiwyg')
                <textarea name="{{ $inputName }}"
                          rows="5"
                          placeholder="{{ $field['label'] }}"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          {{ $isRequired }}>{{ $field['value'] ?? '' }}</textarea>
                @break

            @case('color')
                <input type="color"
                       name="{{ $inputName }}"
                       value="{{ $field['value'] ?? '#000000' }}"
                       class="w-16 h-10 rounded cursor-pointer border-0"
                       {{ $isRequired }}>
                @break

            @case('date')
                <input type="date"
                       name="{{ $inputName }}"
                       value="{{ $field['value'] ?? '' }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       {{ $isRequired }}>
                @break

            @case('datetime')
                <input type="datetime-local"
                       name="{{ $inputName }}"
                       value="{{ $field['value'] ?? '' }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       {{ $isRequired }}>
                @break

            @case('select')
                @php $options = $field['config']['options'] ?? []; @endphp
                <select name="{{ $inputName }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        {{ $isRequired }}>
                    <option value="">{{__('Select an option')}}</option>
                    @foreach($options as $value => $label)
                        <option value="{{ $value }}" {{ ($field['value'] ?? '') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @break

            @case('checkbox')
                @php
                    $options = $field['config']['options'] ?? [];
                    $selectedValues = is_array($field['value']) ? $field['value'] : [];
                @endphp
                <div class="space-y-2">
                    @foreach($options as $value => $label)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox"
                                   name="{{ $inputName }}[]"
                                   value="{{ $value }}"
                                   {{ in_array($value, $selectedValues) ? 'checked' : '' }}
                                   {{ $field['is_required'] ? 'required' : '' }}
                                   class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @break

            @case('radio')
                @php $options = $field['config']['options'] ?? []; @endphp
                <div class="space-y-2">
                    @foreach($options as $value => $label)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio"
                                   name="{{ $inputName }}"
                                   value="{{ $value }}"
                                   {{ ($field['value'] ?? '') == $value ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 focus:ring-2 focus:ring-blue-500"
                                   {{ $isRequired }}>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @break

            @case('image')
                <div class="max-w-[220px]">
                    <x-common.file-manager-upload
                        name="{{ $inputName }}"
                        :value="$field['value'] ?? ''"
                        label="{{ $field['label'] }}"
                        :required="$field['is_required']"
                        width="200"
                        height="120"
                    />
                </div>
                @break

            @case('file')
                <input type="text"
                       name="{{ $inputName }}"
                       value="{{ $field['value'] ?? '' }}"
                       placeholder="https://example.com/file.pdf"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       {{ $isRequired }}>
                @break

            @case('responsive_image')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- Desktop Image --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Desktop Image
                        </label>

                        <div class="max-w-[180px]">
                            <x-common.file-manager-upload
                                name="data[{{ $field['input_name'] }}][desktop]"
                                :value="$field['value']['desktop'] ?? ''"
                                label="Desktop"
                                :required="$field['is_required']"
                                width="200"
                                height="120"
                            />
                        </div>
                    </div>

                    {{-- Mobile Image --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Mobile Image
                        </label>

                        <div class="max-w-[180px]">
                            <x-common.file-manager-upload
                                name="data[{{ $field['input_name'] }}][mobile]"
                                :value="$field['value']['mobile'] ?? ''"
                                label="Mobile"
                                :required="$field['is_required']"
                                width="120"
                                height="160"
                            />
                        </div>
                    </div>

                </div>

                @break

            @case('repeater')
            @case('repeatable')
                @include('admin.section-translation.partials.repeater-display', [
                    'field' => $field,
                    'language' => $language
                ])
                @break

            @default
                <input type="text"
                       name="{{ $inputName }}"
                       value="{{ $field['value'] ?? '' }}"
                       placeholder="{{ $field['label'] }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       {{ $isRequired }}>
        @endswitch
    </div>
</div>
