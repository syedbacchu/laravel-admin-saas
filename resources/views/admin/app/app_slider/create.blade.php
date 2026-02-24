<x-layout.default>
    @section('title', $pageTitle)
    <div class="panel mt-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">{{ $pageTitle }}</h1>
        <div>
            <form method="POST"  action="{{ route('appSlider.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="mb-2">
                        <label for="title" class="">{{ __('Title') }}</label>
                        <input type="hidden" name="type" value="{{ $type }}">
                        @if(isset($item))
                            <input type="hidden" name="edit_id" value="{{ $item->id }}">
                        @endif
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input name="title" type="text" @if(isset($item)) value="{{ $item->title }}" @else value="{{ old('title') }}" @endif class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="subtitle" class="">{{ __('Sub Title') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input name="subtitle" type="text" @if(isset($item)) value="{{ $item->subtitle }}" @else value="{{ old('subtitle') }}" @endif class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="mb-2">
                        <label for="offer" class="">{{ __('Offer') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input name="offer" type="text" @if(isset($item)) value="{{ $item->offer }}" @else value="{{ old('offer') }}" @endif class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="link" class="">{{ __('Link') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input name="link" type="text" @if(isset($item)) value="{{ $item->link }}" @else value="{{ old('link') }}" @endif class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Serial Input -->
                    <div class="mb-4">
                        <label for="serial" class="block text-gray-700 font-medium mb-2">{{ __('Serial') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input
                                name="serial"
                                type="text"
                                @if(isset($item)) value="{{ $item->serial }}" @else value="{{ old('serial') }}" @endif
                                class="flex-1 border border-gray-300 rounded-r-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                            />
                        </div>
                    </div>

                    <div
                        x-data="fileManager('{{ $item->photo ?? '' }}', 'photo')"
                        class="space-y-2"
                    >

                        <label class="font-semibold text-gray-700">
                            Banner Image
                        </label>

                        <!-- Trigger File Manager -->
                        <button
                            type="button"
                            @click="$dispatch('open-file-manager', { callback: callbackName })"
                            class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg
                   shadow hover:bg-blue-700"
                        >
                            Choose Image
                        </button>

                        <!-- Hidden input -->
                        <input type="hidden" name="photo" x-model="fileUrl">

                        <!-- Preview -->
                        <template x-if="filePreview">
                            <div class="mt-3">
                                <img
                                    :src="filePreview"
                                    class="rounded-xl border object-cover shadow-sm"
                                    width="200"
                                >
                            </div>
                        </template>

                    </div>

                </div>
                @if(isset($item)) @customFields($item) @else @customFields(\App\Models\Slider::class) @endif

                <div>
                    <button type="submit" class="btn btn-secondary mt-6">Submit</button>
                </div>
            </form>
        </div>
    </div>

<script>
</script>
</x-layout.default>
