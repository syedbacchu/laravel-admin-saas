@php
    $inputName = 'data' . ($parentName ? '[' . $parentName . '][' . $repeaterFieldName . ']' : '[' . $repeaterFieldName . ']');
    $isRequired = $field->is_required ? 'required' : '';
@endphp

<div class="bg-white border border-gray-200 rounded p-3">
    <label class="block text-sm font-medium text-gray-700 mb-1">
        {{ $field->label }}
        @if($field->is_required)
            <span class="text-red-500">*</span>
        @endif
    </label>

    {{-- Dynamic field rendering with Alpine.js binding --}}
    @switch($field->field_type)
        @case('text')
            <input type="text"
                   :name="'{{ $inputName }}[' + $index + '][{{ $field->name }}]'"
                   x-model="items[$index]['{{ $field->name }}']"
                   placeholder="{{ $field->label }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                   {{ $isRequired }}>
            @break

        @case('textarea')
            <textarea :name="'{{ $inputName }}[' + $index + '][{{ $field->name }}]'"
                      x-model="items[$index]['{{ $field->name }}']"
                      rows="2"
                      placeholder="{{ $field->label }}"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                      {{ $isRequired }}></textarea>
            @break

        @case('number')
            <input type="number"
                   :name="'{{ $inputName }}[' + $index + '][{{ $field->name }}]'"
                   x-model="items[$index]['{{ $field->name }}']"
                   placeholder="{{ $field->label }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                   {{ $isRequired }}>
            @break

        @case('email')
            <input type="email"
                   :name="'{{ $inputName }}[' + $index + '][{{ $field->name }}]'"
                   x-model="items[$index]['{{ $field->name }}']"
                   placeholder="{{ $field->label }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                   {{ $isRequired }}>
            @break

        @case('url')
            <input type="url"
                   :name="'{{ $inputName }}[' + $index + '][{{ $field->name }}]'"
                   x-model="items[$index]['{{ $field->name }}']"
                   placeholder="https://"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                   {{ $isRequired }}>
            @break

        @case('wysiwyg')
            <textarea :name="'{{ $inputName }}[' + $index + '][{{ $field->name }}]'"
                      x-model="items[$index]['{{ $field->name }}']"
                      rows="3"
                      placeholder="{{ $field->label }}"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                      {{ $isRequired }}></textarea>
            @break

        @case('color')
            <input type="color"
                   :name="'{{ $inputName }}[' + $index + '][{{ $field->name }}]'"
                   x-model="items[$index]['{{ $field->name }}']"
                   class="w-12 h-8 rounded cursor-pointer border-0"
                   {{ $isRequired }}>
            @break

        @case('date')
            <input type="date"
                   :name="'{{ $inputName }}[' + $index + '][{{ $field->name }}]'"
                   x-model="items[$index]['{{ $field->name }}']"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                   {{ $isRequired }}>
            @break

        @case('datetime')
            <input type="datetime-local"
                   :name="'{{ $inputName }}[' + $index + '][{{ $field->name }}]'"
                   x-model="items[$index]['{{ $field->name }}']"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                   {{ $isRequired }}>
            @break

        @case('select')
            @php
                $options = $field->config['options'] ?? [];
            @endphp
            <select :name="'{{ $inputName }}[' + $index + '][{{ $field->name }}]'"
                    x-model="items[$index]['{{ $field->name }}']"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                    {{ $isRequired }}>
                <option value="">{{__('Select an option')}}</option>
                @foreach($options as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @break

        @case('checkbox')
            @php
                $options = $field->config['options'] ?? [];
            @endphp
            <div class="space-y-1">
                @foreach($options as $value => $label)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox"
                               :name="'{{ $inputName }}[' + $index + '][{{ $field->name }}'][]'"
                               :value="'{{ $value }}'"
                               x-model="items[$index]['{{ $field->name }}']"
                               {{ $field->is_required ? 'required' : '' }}
                               class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                        <span class="text-sm">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            @break

        @case('radio')
            @php
                $options = $field->config['options'] ?? [];
            @endphp
            <div class="space-y-1">
                @foreach($options as $value => $label)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio"
                               :name="'{{ $inputName }}[' + $index + '][{{ $field->name }}]'"
                               :value="'{{ $value }}'"
                               x-model="items[$index]['{{ $field->name }}']"
                               class="w-4 h-4 text-blue-600 focus:ring-2 focus:ring-blue-500"
                               {{ $isRequired }}>
                        <span class="text-sm">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            @break

        @case('image')
            <div>
                <input type="text"
                       :name="'{{ $inputName }}[' + $index + '][{{ $field->name }}]'"
                       x-model="items[$index]['{{ $field->name }}']"
                       placeholder="https://example.com/image.jpg"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                       {{ $isRequired }}>
                <div x-show="items[$index]['{{ $field->name }}']" class="mt-2">
                    <img :src="items[$index]['{{ $field->name }}']" alt="{{ $field->label }}" class="h-20 w-auto rounded border border-gray-200" onerror="this.style.display='none'">
                </div>
            </div>
            @break

        @case('file')
            <input type="text"
                   :name="'{{ $inputName }}[' + $index + '][{{ $field->name }}]'"
                   x-model="items[$index]['{{ $field->name }}']"
                   placeholder="https://example.com/file.pdf"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                   {{ $isRequired }}>
            @break

        @case('responsive_image')
            <div class="space-y-2">
                <div>
                    <label class="text-xs text-gray-600">Desktop Image</label>
                    <input type="text"
                           :name="'{{ $inputName }}[' + $index + '][{{ $field->name }}][desktop]'"
                           x-model="items[$index]['{{ $field->name }}'].desktop"
                           placeholder="Desktop image URL"
                           class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                           {{ $isRequired }}>
                    <div x-show="items[$index]['{{ $field->name }}']?.desktop" class="mt-1">
                        <img :src="items[$index]['{{ $field->name }}'].desktop" alt="Desktop" class="h-16 w-auto rounded border border-gray-200" onerror="this.style.display='none'">
                    </div>
                </div>
                <div>
                    <label class="text-xs text-gray-600">Mobile Image</label>
                    <input type="text"
                           :name="'{{ $inputName }}[' + $index + '][{{ $field->name }}][mobile]'"
                           x-model="items[$index]['{{ $field->name }}'].mobile"
                           placeholder="Mobile image URL"
                           class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                           {{ $isRequired }}>
                    <div x-show="items[$index]['{{ $field->name }}']?.mobile" class="mt-1">
                        <img :src="items[$index]['{{ $field->name }}'].mobile" alt="Mobile" class="h-16 w-auto rounded border border-gray-200" onerror="this.style.display='none'">
                    </div>
                </div>
            </div>
            @break

        @default
            <input type="text"
                   :name="'{{ $inputName }}[' + $index + '][{{ $field->name }}]'"
                   x-model="items[$index]['{{ $field->name }}']"
                   placeholder="{{ $field->label }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                   {{ $isRequired }}>
    @endswitch
</div>