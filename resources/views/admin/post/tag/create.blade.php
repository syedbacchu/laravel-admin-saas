<x-layout.default>
    @section('title', $pageTitle)

    <div class="panel mt-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">
            {{ $pageTitle }}
        </h1>

        <div>
            <form
                method="POST"
                action="{{ route('tag.store') }}"
            >
                @csrf
                @if(isset($item))
                    <input type="hidden" name="edit_id" value="{{ $item->id }}">
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">{{ __('Name') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input
                                name="name"
                                data-slug-source
                                type="text"
                                value="{{ $item->name ?? old('name') }}"
                                class="form-input ltr:rounded-l-none rtl:rounded-r-none"
                                required
                            />
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">{{ __('Slug') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input
                                name="slug"
                                data-slug-target
                                type="text"
                                value="{{ $item->slug ?? old('slug') }}"
                                class="form-input ltr:rounded-l-none rtl:rounded-r-none"
                            />
                        </div>
                    </div>
                </div>

                @if(isset($item))
                    @customFields($item)
                @else
                    @customFields(\App\Models\Tag::class)
                @endif

                <div>
                    <button type="submit" class="btn btn-secondary mt-6">
                        {{ __('Submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout.default>
