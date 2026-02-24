<x-layout.default>
    @section('title', $pageTitle)
    <div class="panel mt-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">{{ $pageTitle }}</h1>
        <div>
            <form method="POST"  action="{{ route('settings.fields.store') }}" enctype="multipart/form-data">
                @csrf
                <div x-data="{ type: '{{ old('type') }}' }">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="mb-2">
                        <label for="title" class="">{{ __('Group') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input type="text"
                                   name="group"
                                   class="form-input w-full"
                                   value="{{ old('group') }}"
                                   placeholder="sms">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="subtitle" class="">{{ __('Label') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input type="text"
                                   name="label"
                                   data-slug-source
                                   class="form-input w-full"
                                   value="{{ old('label') }}"
                                   placeholder="SMS Provider">
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="mb-2">
                        <label for="offer" class="">{{ __('Slug') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input type="text"
                                   name="slug"
                                   data-slug-target
                                   class="form-input w-full"
                                   value="{{ old('slug') }}"
                                   placeholder="sms_provider">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="serial" class="block text-gray-700 font-medium mb-2">{{ __('Validation Rules') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input type="text"
                                   name="validation_rules"
                                   class="form-input w-full"
                                   value="{{ old('validation_rules') }}"
                                   placeholder="required|string|max:255">
                        </div>
                    </div>

                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="mb-2">
                        <label for="type" class="">{{ __('Type') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <select name="type"
                                    class="form-select w-full"
                                    x-model="type">
                                <option value="">-- Select Type --</option>
                                <option value="text">Text</option>
                                <option value="password">Password</option>
                                <option value="number">Number</option>
                                <option value="select">Select</option>
                                <option value="checkbox">Checkbox</option>
                                <option value="radio">Radio</option>
                                <option value="file">File</option>
                                <option value="textarea">Textarea</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4" x-show="['select','checkbox','radio'].includes(type)" x-cloak>
                        <label for="serial" class="block text-gray-700 font-medium mb-2">{{ __('Options (comma separated)') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <textarea name="options"
                                      class="form-textarea w-full"
                                      placeholder="twilio, sslwireless, nexmo">{{ old('options') }}</textarea>
                        </div>
                    </div>

                </div>

                <div>
                    <button type="submit" class="btn btn-secondary mt-6">{{__('Submit')}}</button>
                </div>
                </div>
            </form>
        </div>
    </div>

</x-layout.default>
