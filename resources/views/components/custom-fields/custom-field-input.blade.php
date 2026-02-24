<div class="form-group mb-3">
    <label class="font-semibold mb-1 block">{{ $field->label }}</label>

    @php
        $options = is_array($field->options)
            ? $field->options
            : array_map('trim', explode(',', $field->options ?? ''));
    @endphp

    {{-- TEXT --}}
    @if($field->type === 'text')
        <input
            type="text"
            name="custom_fields[{{ $field->id }}]"
            value="{{ old("custom_fields.$field->id", $value) }}"
            class="form-input w-full"
        />

        {{-- TEXTAREA --}}
    @elseif($field->type === 'textarea')
        <textarea
            name="custom_fields[{{ $field->id }}]"
            class="form-textarea w-full"
        >{{ old("custom_fields.$field->id", $value) }}</textarea>

        {{-- NUMBER --}}
    @elseif($field->type === 'number')
        <input
            type="number"
            name="custom_fields[{{ $field->id }}]"
            value="{{ old("custom_fields.$field->id", $value) }}"
            class="form-input w-full"
        />

        {{-- SELECT --}}
    @elseif($field->type === 'select')
        <select
            name="custom_fields[{{ $field->id }}]"
            class="form-select w-full"
        >
            <option value="">Select</option>
            @foreach($options as $option)
                <option
                    value="{{ $option }}"
                    @selected(old("custom_fields.$field->id", $value) == $option)
                >
                    {{ $option }}
                </option>
            @endforeach
        </select>

        {{-- RADIO --}}
    @elseif($field->type === 'radio')
        <div class="flex flex-wrap gap-4 mt-2">
            @foreach($options as $option)
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input
                        type="radio"
                        name="custom_fields[{{ $field->id }}]"
                        value="{{ $option }}"
                        class="form-radio text-blue-600"
                        @checked(old("custom_fields.$field->id", $value) == $option)
                    >
                    <span class="text-sm text-gray-700">{{ $option }}</span>
                </label>
            @endforeach
        </div>


        {{-- CHECKBOX (MULTI VALUE) --}}
    @elseif($field->type === 'checkbox')
        @php
            $selected = is_array($value)
                ? $value
                : json_decode($value ?? '[]', true);
        @endphp

        <div class="flex flex-wrap gap-4 mt-2">
            @foreach($options as $option)
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        name="custom_fields[{{ $field->id }}][]"
                        value="{{ $option }}"
                        class="form-checkbox text-blue-600"
                        @checked(in_array($option, old("custom_fields.$field->id", $selected ?? [])))
                    >
                    <span class="text-sm text-gray-700">{{ $option }}</span>
                </label>
            @endforeach
        </div>

    @elseif($field->type === 'file')
        <div
            x-data="fileManager(
            '{{ $value ? $value : '' }}',
            'custom_fields_{{ $field->id }}'
        )"
            class="space-y-2"
        >
            <button
                type="button"
                @click="$dispatch('open-file-manager', { callback: callbackName })"
                class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg shadow hover:bg-blue-700"
            >
                Choose File
            </button>

            <!-- Hidden input -->
            <input
                type="hidden"
                name="custom_fields[{{ $field->id }}]"
                :value="fileUrl"
            >

            <!-- Preview -->
            <template x-if="filePreview">
                <div class="mt-2">
                    <img
                        x-show="filePreview.match(/\.(jpg|jpeg|png|webp|gif)$/i)"
                        :src="filePreview"
                        class="h-24 rounded border object-cover"
                    >

                    <a
                        x-show="!filePreview.match(/\.(jpg|jpeg|png|webp|gif)$/i)"
                        :href="filePreview"
                        target="_blank"
                        class="text-blue-600 underline text-sm"
                    >
                        View File
                    </a>
                </div>
            </template>
        </div>
    @endif

</div>
