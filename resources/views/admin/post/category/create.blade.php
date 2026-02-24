<x-layout.default>
    @section('title', $pageTitle)

    <div class="panel mt-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">
            {{ $pageTitle }}
        </h1>

        <div>
            <form
                method="POST"
                action="{{ route('postCategory.store') }}"
                enctype="multipart/form-data"
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">{{ __('Parent Category') }}</label>
                        <select name="parent_id" class="form-select w-full">
                            <option value="">{{ __('No Parent') }}</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}"
                                    @selected(($item->parent_id ?? old('parent_id')) == $parent->id)>
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">{{ __('Serial') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input
                                name="serial"
                                type="number"
                                value="{{ $item->serial ?? old('serial', 0) }}"
                                class="form-input ltr:rounded-l-none rtl:rounded-r-none"
                            />
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">{{ __('Meta Title') }}</label>
                        <input
                            name="meta_title"
                            type="text"
                            value="{{ $item->meta_title ?? old('meta_title') }}"
                            class="form-input w-full"
                        />
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">{{ __('Meta Keywords') }}</label>
                        <input
                            name="meta_keywords"
                            type="text"
                            value="{{ $item->meta_keywords ?? old('meta_keywords') }}"
                            class="form-input w-full"
                        />
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Meta Description') }}</label>
                    <textarea
                        name="meta_description"
                        rows="3"
                        class="form-textarea w-full"
                    >{{ $item->meta_description ?? old('meta_description') }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div
                        x-data="postCategoryImage('{{ $item->image ?? '' }}')"
                        class="space-y-2 mt-2"
                    >
                        <label class="font-semibold text-gray-700">{{ __('Category Image') }}</label>

                        <button
                            type="button"
                            x-on:click="$dispatch('open-file-manager', { callback: 'postCategoryImageSelected' })"
                            class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg shadow hover:bg-blue-700"
                        >
                            {{ __('Choose Image') }}
                        </button>

                        <input type="hidden" name="image" x-model="image">

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

                    <div class="mb-4 mt-2">
                        <label class="block text-gray-700 font-medium mb-2">{{ __('Status') }}</label>
                        <select name="status" class="form-select w-full">
                            <option value="1" @selected(($item->status ?? 1) == 1)>Active</option>
                            <option value="0" @selected(($item->status ?? 1) == 0)>Inactive</option>
                        </select>
                    </div>
                </div>

                @if(isset($item))
                    @customFields($item)
                @else
                    @customFields(\App\Models\PostCategory::class)
                @endif

                <div>
                    <button type="submit" class="btn btn-secondary mt-6">
                        {{ __('Submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function postCategoryImage(existingImage = '') {
            return {
                image: existingImage,
                preview: existingImage ? existingImage : '',
                init() {
                    window.addEventListener('postCategoryImageSelected', (e) => {
                        this.image = e.detail.url;
                        this.preview = e.detail.url;
                    });
                }
            }
        }
    </script>
</x-layout.default>
