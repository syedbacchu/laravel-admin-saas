<x-layout.default>
    @section('title', 'Edit Setting Field')

    <div class="panel mt-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">{{ $pageTitle }}</h1>
        <form method="POST" action="{{ route('settings.fields.update', $field->id) }}">
            @csrf
            @method('PUT')

            <div x-data="{ type: '{{ old('type', $field->type) }}' }" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                {{-- GROUP --}}
                <div class="mb-2">
                    <label class="block mb-1 font-medium">{{__('Group')}}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input type="text"
                               name="group"
                               class="form-input w-full"
                               value="{{ old('group', $field->group) }}">
                    </div>
                </div>

                {{-- LABEL --}}
                <div class="mb-2">
                    <label class="block mb-1 font-medium">{{__('Label')}}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                    <input type="text"
                           name="label"
                           data-slug-source
                           class="form-input w-full"
                           value="{{ old('label', $field->label) }}">
                    </div>
                </div>

                {{-- SLUG --}}
                <div class="mb-2">
                    <label class="block mb-1 font-medium">{{__('Slug')}}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                    <input type="text"
                           name="slug"
                           data-slug-target
                           class="form-input w-full"
                           value="{{ old('slug', $field->slug) }}">
                    </div>
                </div>

                {{-- TYPE --}}
                <div class="mb-2">
                    <label class="block mb-1 font-medium">{{__('Type')}}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                    <select name="type"
                            class="form-select w-full"
                            x-model="type">
                        @foreach(['text','password','number','select','checkbox','radio','file','textarea'] as $t)
                            <option value="{{ $t }}"
                                @selected(old('type', $field->type) === $t)>
                                {{ ucfirst($t) }}
                            </option>
                        @endforeach
                    </select>
                    </div>
                </div>

                {{-- OPTIONS --}}
                <div x-show="['select','checkbox','radio'].includes(type)" x-cloak class="mb-2">
                    <label class="block mb-1 font-medium">
                        {{__('Options (comma separated)')}}
                    </label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                    <textarea name="options"
                              class="form-textarea w-full">
{{ old('options', is_array($field->options) ? implode(',', $field->options) : '') }}
                    </textarea>
                    </div>
                </div>

                {{-- VALIDATION --}}
                <div class="mb-2">
                    <label class="block mb-1 font-medium">
                        {{__('Validation Rules')}}
                    </label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                    <input type="text"
                           name="validation_rules"
                           class="form-input w-full"
                           value="{{ old('validation_rules', $field->validation_rules) }}">
                    </div>
                </div>
                </div>
                {{-- SUBMIT --}}
                <div class="pt-4">
                    <button class="btn btn-secondary mt-6">
                        {{__('Update Field')}}
                    </button>
                </div>


            </div>
        </form>
    </div>
</x-layout.default>
