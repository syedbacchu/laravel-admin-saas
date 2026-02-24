<x-layout.default>
    @section('title', $pageTitle)

    <div class="panel mt-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">
            {{ $pageTitle }}
        </h1>

        <div>
            <form
                method="POST"
                action="{{ $function_type === 'create'
                    ? route('faqCategory.store')
                    : route('faqCategory.update', $item->id) }}"
                enctype="multipart/form-data"
            >
                @csrf
                @if($function_type === 'update')
                    @method('PUT')
                    <input type="hidden" name="edit_id" value="{{ $item->id }}">
                @endif

                <!-- Name -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Name') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input
                            name="name"
                            type="text"
                            value="{{ $item->name ?? old('name') }}"
                            class="form-input ltr:rounded-l-none rtl:rounded-r-none"
                            required
                        />
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Description') }}</label>
                    <textarea
                        name="description"
                        rows="3"
                        class="form-textarea w-full"
                    >{{ $item->description ?? old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Sort Order -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">{{ __('Sort Order') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input
                                name="sort_order"
                                type="number"
                                value="{{ $item->sort_order ?? old('sort_order', 0) }}"
                                class="form-input ltr:rounded-l-none rtl:rounded-r-none"
                            />
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">{{ __('Status') }}</label>
                        <select name="status" class="form-select w-full">
                            <option value="1" @selected(($item->status ?? 1) == 1)>Active</option>
                            <option value="0" @selected(($item->status ?? 1) == 0)>Inactive</option>
                        </select>
                    </div>

                </div>

                <!-- Image Upload -->
                <div
                    x-data="faqCategoryImage('{{ $item->image ?? '' }}')"
                    class="space-y-2 mt-4"
                >
                    <label class="font-semibold text-gray-700">{{ __('Category Image') }}</label>

                    <button
                        type="button"
                        x-on:click="$dispatch('open-file-manager', { callback: 'faqImageSelected' })"
                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg shadow hover:bg-blue-700"
                    >
                        Choose Image
                    </button>

                    <!-- Hidden input -->
                    <input type="hidden" name="image" x-model="image" @if($function_type==='create') required @endif>

                    <!-- Preview -->
                    <template x-if="preview">
                        <div class="mt-3">
                            <img
                                :src="preview"
                                class="rounded-xl border object-cover shadow-sm"
                                width="200"
                            >
                        </div>
                    </template>
                </div>

                {{-- Custom Fields --}}
                @if(isset($item))
                    @customFields($item)
                @else
                    @customFields(\App\Models\FaqCategory::class)
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

<script>
    function faqCategoryImage(existingImage = '') {
        return {
            image: existingImage,
            preview: existingImage ? existingImage : '',

            init() {
                window.addEventListener('faqImageSelected', (e) => {
                    this.image = e.detail.url;
                    this.preview = e.detail.url;
                });
            }
        }
    }
</script>

</x-layout.default>
