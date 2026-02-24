<x-layout.default>
    @section('title', $pageTitle)
    <div class="mt-8 bg-white shadow-xl rounded-2xl p-6 border border-gray-100">

        <div class="flex items-center justify-between mb-6">
            <h5 class="text-2xl font-bold text-gray-800">{{ $pageTitle }}</h5>
        </div>

        <div class="panel bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="flex flex-col md:flex-row" x-data="{ tab: '{{ $activeTab }}' }">

                <!-- LEFT TABS -->
                <aside class="w-full md:w-56 border-b md:border-b-0 md:border-r border-gray-200">
                    <ul class="p-4 space-y-1">
                        @foreach($groups as $group => $fields)
                            <li>
                                <button type="button"
                                        @click="tab = '{{ $group }}'"
                                        class="w-full flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-medium transition"
                                        :class="tab === '{{ $group }}'
                                    ? 'bg-success/10 text-success'
                                    : 'text-gray-600 hover:bg-gray-100'">

                            <span class="h-2 w-2 rounded-full"
                                  :class="tab === '{{ $group }}' ? 'bg-success' : 'bg-transparent'">
                            </span>

                                    {{ ucfirst($group) }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </aside>

                <!-- CONTENT -->
                <section class="flex-1 p-6">
                    @foreach($groups as $group => $fields)
                        <div x-show="tab === '{{ $group }}'" x-cloak>

                            <form method="POST"
                                  action="{{ route('settings.update', $group) }}"
                                  enctype="multipart/form-data">
                                @csrf

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                    @foreach($fields as $field)
                                        @php
                                            $value = $values[$field->slug] ?? null;
                                        @endphp

                                        <div>
                                            <label class="block mb-1 text-sm font-medium text-gray-700">
                                                {{ __($field->label) }}
                                            </label>

                                            {{-- TEXT / NUMBER / PASSWORD --}}
                                            @if(in_array($field->type, ['text','number','password']))
                                                <input type="{{ $field->type }}"
                                                       name="{{ $field->slug }}"
                                                       value="{{ old($field->slug, $value) }}"
                                                       class="form-input w-full">
                                            @endif

                                            @if(in_array($field->type, ['textarea']))
                                                <textarea name="{{ $field->slug }}" class="form-textarea">{{ old($field->slug, $value) }}</textarea>

                                            @endif

                                            {{-- SELECT --}}
                                            @if($field->type === 'select')
                                                <select name="{{ $field->slug }}"
                                                        class="form-select w-full">
                                                    @foreach($field->options as $opt)
                                                        <option value="{{ $opt }}"
                                                            @selected(old($field->slug, $value) == $opt)>
                                                            {{ ucfirst($opt) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @endif

                                            {{-- RADIO --}}
                                            @if($field->type === 'radio')
                                                <div class="flex flex-wrap gap-4 mt-1">
                                                    @foreach($field->options as $opt)
                                                        <label class="inline-flex items-center">
                                                            <input type="radio"
                                                                   name="{{ $field->slug }}"
                                                                   value="{{ $opt }}"
                                                                   class="form-radio"
                                                                @checked(old($field->slug, $value) == $opt)>
                                                            <span class="ml-1">{{ ucfirst($opt) }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @endif

                                            {{-- CHECKBOX --}}
                                            @if($field->type === 'checkbox')
                                                @php
                                                    $vals = is_array($value)
                                                        ? $value
                                                        : json_decode($value, true) ?? [];
                                                @endphp

                                                <div class="flex flex-wrap gap-4 mt-1">
                                                    @foreach($field->options as $opt)
                                                        <label class="inline-flex items-center">
                                                            <input type="checkbox"
                                                                   name="{{ $field->slug }}[]"
                                                                   value="{{ $opt }}"
                                                                   class="form-checkbox"
                                                                @checked(in_array($opt, $vals))>
                                                            <span class="ml-1">{{ ucfirst($opt) }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if($field->type === 'file')
                                                <div
                                                    x-data="fileManager(
                                                        '{{ old($field->slug, $value) }}',
                                                        '{{ $field->slug }}'
                                                    )"
                                                    class="space-y-2"
                                                >

                                                    <!-- Trigger File Manager -->
                                                    <button
                                                        type="button"
                                                        @click="$dispatch('open-file-manager', { callback: callbackName })"
                                                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg shadow hover:bg-blue-700"
                                                    >
                                                        Choose Image
                                                    </button>

                                                    <!-- Hidden Input (URL will be submitted) -->
                                                    <input type="hidden"
                                                           :name="fieldName"
                                                           x-model="fileUrl">

                                                    <!-- Preview -->
                                                    <template x-if="filePreview">
                                                        <img :src="filePreview"
                                                             class="h-20 mt-2 rounded border object-cover">
                                                    </template>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach

                                </div>

                                <!-- ACTION -->
                                <div class="mt-8">
                                    <button class="btn btn-secondary px-6">
                                        {{ __('Update') }}
                                    </button>
                                </div>

                            </form>
                        </div>
                    @endforeach
                </section>
            </div>
        </div>
    </div>

</x-layout.default>
