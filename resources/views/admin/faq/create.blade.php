<x-layout.default>
    @section('title', $pageTitle)

    <div class="panel mt-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">
            {{ $pageTitle }}
        </h1>

        <div>
            <form method="POST" action="{{ $function_type === 'create'
    ? route('faq.store')
    : route('faq.update', $item->id) }}" enctype="multipart/form-data">
                @csrf
                @if($function_type === 'update')
                    @method('PUT')
                    <input type="hidden" name="edit_id" value="{{ $item->id }}">
                @endif

                <!-- Category -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Category') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <select name="category_id" class="form-select w-full" required>
                            <option value="">{{ __('Select Category') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(($item->category_id ?? old('category_id')) == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Question -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Question') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input type="text" name="question" value="{{ $item->question ?? old('question') }}"
                            class="form-input w-full" required />
                    </div>
                </div>

                <!-- Answer -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Answer') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <textarea name="answer" rows="4" class="form-textarea w-full"
                            required>{{ $item->answer ?? old('answer') }}</textarea>
                    </div>
                </div>

                <!-- Attestment -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Attestment') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input type="file" name="attestment" value="{{ $item->attestment ?? old('attestment') }}"
                            class="form-input w-full" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Sort Order -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">{{ __('Sort Order') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input name="sort_order" type="number"
                                value="{{ $item->sort_order ?? old('sort_order', 0) }}"
                                class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">{{ __('Status') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <select name="status" class="form-select w-full">
                                <option value="1" @selected(($item->status ?? 1) == 1)>Active</option>
                                <option value="0" @selected(($item->status ?? 1) == 0)>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Custom Fields --}}
                @if(isset($item))
                    @customFields($item)
                @else
                    @customFields(\App\Models\Faq::class)
                @endif

                <!-- Submit -->
                <div>
                    <button type="submit" class="btn btn-secondary mt-6">
                        {{ __('Submit') }}
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-layout.default>