<x-layout.default>
    @section('title', $pageTitle)
    @vite(['resources/css/easymde.min.css', 'resources/css/font-awesome.min.css'])
    <script src="{{ asset('assets/js/easymde.min.js') }}"></script>

    @php
        $selectedCategoryIds = array_map('intval', (array) ($selectedCategoryIds ?? old('category_ids', [])));
        $selectedTagIds = array_map('intval', (array) ($selectedTagIds ?? old('tag_ids', [])));
    @endphp

    <form
        id="post-form"
        method="POST"
        action="{{ route('post.store') }}"
        class="mt-4"
    >
        @csrf
        @if(isset($item))
            <input type="hidden" name="edit_id" value="{{ $item->id }}">
        @endif

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                {{ $pageTitle }}
            </h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('post.list') }}" class="btn btn-outline-primary">{{ __('Back') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('Save Post') }}</button>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">
                <div class="panel !p-0 overflow-hidden border border-[#e0e6ed] dark:border-[#1b2e4b] markdown-editor">
                    <div class="px-4 py-3 border-b border-[#e0e6ed] dark:border-[#1b2e4b] bg-[#fafafa] dark:bg-[#0b1320]">
                        <h3 class="font-semibold text-base">{{ __('Post Title') }}</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        <input
                            name="title"
                            data-slug-source
                            type="text"
                            value="{{ $item->title ?? old('title') }}"
                            class="form-input text-lg font-semibold"
                            placeholder="{{ __('Add title') }}"
                            required
                        />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs uppercase font-semibold text-gray-500">{{ __('Permalink / Slug') }}</label>
                                <input
                                    name="slug"
                                    data-slug-target
                                    type="text"
                                    value="{{ $item->slug ?? old('slug') }}"
                                    class="form-input mt-1"
                                />
                            </div>
                            <div>
                                <label class="text-xs uppercase font-semibold text-gray-500">{{ __('Post Type') }}</label>
                                <select name="post_type" class="form-select mt-1">
                                    @foreach(['blog', 'article', 'event', 'notice', 'news'] as $type)
                                        <option value="{{ $type }}" @selected(($item->post_type ?? old('post_type', 'blog')) === $type)>
                                            {{ ucfirst($type) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel !p-0 overflow-hidden border border-[#e0e6ed] dark:border-[#1b2e4b]">
                    <div class="px-4 py-3 border-b border-[#e0e6ed] dark:border-[#1b2e4b] bg-[#fafafa] dark:bg-[#0b1320]">
                        <h3 class="font-semibold text-base">{{ __('Content') }}</h3>
                    </div>
                    <div class="p-4">
                        <textarea
                            name="content"
                            id="post-content-editor"
                            class="form-textarea w-full"
                        >{{ $item->content ?? old('content') }}</textarea>
                    </div>
                </div>

                <div class="panel !p-0 overflow-hidden border border-[#e0e6ed] dark:border-[#1b2e4b]">
                    <div class="px-4 py-3 border-b border-[#e0e6ed] dark:border-[#1b2e4b] bg-[#fafafa] dark:bg-[#0b1320]">
                        <h3 class="font-semibold text-base">{{ __('Excerpt & Extra Fields') }}</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        <div>
                            <label class="text-xs uppercase font-semibold text-gray-500">{{ __('Excerpt') }}</label>
                            <textarea name="excerpt" rows="3" class="form-textarea mt-1 w-full">{{ $item->excerpt ?? old('excerpt') }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs uppercase font-semibold text-gray-500">{{ __('Venue') }}</label>
                                <input type="text" name="venue" value="{{ $item->venue ?? old('venue') }}" class="form-input mt-1 w-full">
                            </div>
                            <div>
                                <label class="text-xs uppercase font-semibold text-gray-500">{{ __('Video URL') }}</label>
                                <input type="text" name="video_url" value="{{ $item->video_url ?? old('video_url') }}" class="form-input mt-1 w-full">
                            </div>
                        </div>

                        <div>
                            <label class="text-xs uppercase font-semibold text-gray-500">{{ __('Photos (comma separated URLs)') }}</label>
                            <textarea name="photos" rows="2" class="form-textarea mt-1 w-full">{{ $item->photos ?? old('photos') }}</textarea>
                        </div>
                    </div>
                </div>

                @if(isset($item))
                    @customFields($item)
                @else
                    @customFields(\App\Models\Post::class)
                @endif
            </div>

            <div class="space-y-6 xl:sticky xl:top-20 self-start">
                <div class="panel !p-0 overflow-hidden border border-[#e0e6ed] dark:border-[#1b2e4b]">
                    <div class="px-4 py-3 border-b border-[#e0e6ed] dark:border-[#1b2e4b] bg-[#fafafa] dark:bg-[#0b1320]">
                        <h3 class="font-semibold text-base">{{ __('Publish') }}</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        <div>
                            <label class="text-xs uppercase font-semibold text-gray-500">{{ __('Status') }}</label>
                            <select name="status" class="form-select mt-1 w-full">
                                <option value="draft" @selected(($item->status ?? old('status', 'draft')) === 'draft')>Draft</option>
                                <option value="published" @selected(($item->status ?? old('status')) === 'published')>Published</option>
                                <option value="scheduled" @selected(($item->status ?? old('status')) === 'scheduled')>Scheduled</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-xs uppercase font-semibold text-gray-500">{{ __('Published At') }}</label>
                            <input
                                type="datetime-local"
                                name="published_at"
                                value="{{ old('published_at', isset($item->published_at) ? $item->published_at->format('Y-m-d\\TH:i') : '') }}"
                                class="form-input mt-1 w-full"
                            />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="text-xs uppercase font-semibold text-gray-500">{{ __('Visibility') }}</label>
                                <select name="visibility" class="form-select mt-1 w-full">
                                    <option value="1" @selected(($item->visibility ?? old('visibility', 1)) == 1)>Public</option>
                                    <option value="0" @selected(($item->visibility ?? old('visibility')) == 0)>Private</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs uppercase font-semibold text-gray-500">{{ __('Allow Comments') }}</label>
                                <select name="is_comment_allow" class="form-select mt-1 w-full">
                                    <option value="1" @selected(($item->is_comment_allow ?? old('is_comment_allow', 1)) == 1)>Yes</option>
                                    <option value="0" @selected(($item->is_comment_allow ?? old('is_comment_allow')) == 0)>No</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs uppercase font-semibold text-gray-500">{{ __('Featured') }}</label>
                                <select name="is_featured" class="form-select mt-1 w-full">
                                    <option value="1" @selected(($item->is_featured ?? old('is_featured')) == 1)>Yes</option>
                                    <option value="0" @selected(($item->is_featured ?? old('is_featured', 0)) == 0)>No</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs uppercase font-semibold text-gray-500">{{ __('Serial') }}</label>
                                <input type="number" name="serial" value="{{ $item->serial ?? old('serial', 0) }}" class="form-input mt-1 w-full" />
                            </div>
                            <div>
                                <label class="text-xs uppercase font-semibold text-gray-500">{{ __('Featured Order') }}</label>
                                <input type="number" name="featured_order" value="{{ $item->featured_order ?? old('featured_order', 0) }}" class="form-input mt-1 w-full" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3">
                            <div>
                                <label class="text-xs uppercase font-semibold text-gray-500">{{ __('Event Date') }}</label>
                                <input
                                    type="datetime-local"
                                    name="event_date"
                                    value="{{ old('event_date', isset($item->event_date) ? $item->event_date->format('Y-m-d\\TH:i') : '') }}"
                                    class="form-input mt-1 w-full"
                                />
                            </div>
                            <div>
                                <label class="text-xs uppercase font-semibold text-gray-500">{{ __('Event End Date') }}</label>
                                <input
                                    type="datetime-local"
                                    name="event_end_date"
                                    value="{{ old('event_end_date', isset($item->event_end_date) ? $item->event_end_date->format('Y-m-d\\TH:i') : '') }}"
                                    class="form-input mt-1 w-full"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel !p-0 overflow-hidden border border-[#e0e6ed] dark:border-[#1b2e4b]">
                    <div class="px-4 py-3 border-b border-[#e0e6ed] dark:border-[#1b2e4b] bg-[#fafafa] dark:bg-[#0b1320]">
                        <h3 class="font-semibold text-base">{{ __('Categories') }}</h3>
                    </div>
                    <div class="p-4">
                        <div class="max-h-56 overflow-auto space-y-2 pr-1">
                            @forelse($categories as $category)
                                <label class="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        name="category_ids[]"
                                        value="{{ $category->id }}"
                                        class="form-checkbox"
                                        @checked(in_array((int) $category->id, $selectedCategoryIds, true))
                                    >
                                    <span>{{ $category->name }}</span>
                                </label>
                            @empty
                                <p class="text-sm text-gray-500">{{ __('No category found') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="panel !p-0 overflow-hidden border border-[#e0e6ed] dark:border-[#1b2e4b]">
                    <div class="px-4 py-3 border-b border-[#e0e6ed] dark:border-[#1b2e4b] bg-[#fafafa] dark:bg-[#0b1320]">
                        <h3 class="font-semibold text-base">{{ __('Tags') }}</h3>
                    </div>
                    <div class="p-4">
                        <div class="max-h-56 overflow-auto space-y-2 pr-1">
                            @forelse($tags as $tag)
                                <label class="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        name="tag_ids[]"
                                        value="{{ $tag->id }}"
                                        class="form-checkbox"
                                        @checked(in_array((int) $tag->id, $selectedTagIds, true))
                                    >
                                    <span>{{ $tag->name }}</span>
                                </label>
                            @empty
                                <p class="text-sm text-gray-500">{{ __('No tag found') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="panel !p-0 overflow-hidden border border-[#e0e6ed] dark:border-[#1b2e4b]">
                    <div class="px-4 py-3 border-b border-[#e0e6ed] dark:border-[#1b2e4b] bg-[#fafafa] dark:bg-[#0b1320]">
                        <h3 class="font-semibold text-base">{{ __('Featured Images') }}</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        <div x-data="fileManager('{{ $item->thumbnail_img ?? old('thumbnail_img', '') }}', 'thumbnail_img')" class="space-y-2">
                            <label class="text-xs uppercase font-semibold text-gray-500">{{ __('Thumbnail') }}</label>
                            <button type="button"
                                    x-on:click="$dispatch('open-file-manager', { callback: callbackName })"
                                    class="btn btn-outline-primary btn-sm w-full">
                                {{ __('Choose Thumbnail') }}
                            </button>
                            <input type="hidden" name="thumbnail_img" x-model="fileUrl">
                            <template x-if="filePreview">
                                <img :src="filePreview" class="rounded-lg border object-cover w-full max-h-[160px]">
                            </template>
                        </div>

                        <div x-data="fileManager('{{ $item->featured_img ?? old('featured_img', '') }}', 'featured_img')" class="space-y-2">
                            <label class="text-xs uppercase font-semibold text-gray-500">{{ __('Featured') }}</label>
                            <button type="button"
                                    x-on:click="$dispatch('open-file-manager', { callback: callbackName })"
                                    class="btn btn-outline-primary btn-sm w-full">
                                {{ __('Choose Featured Image') }}
                            </button>
                            <input type="hidden" name="featured_img" x-model="fileUrl">
                            <template x-if="filePreview">
                                <img :src="filePreview" class="rounded-lg border object-cover w-full max-h-[160px]">
                            </template>
                        </div>
                    </div>
                </div>

                <div class="panel !p-0 overflow-hidden border border-[#e0e6ed] dark:border-[#1b2e4b]">
                    <div class="px-4 py-3 border-b border-[#e0e6ed] dark:border-[#1b2e4b] bg-[#fafafa] dark:bg-[#0b1320]">
                        <h3 class="font-semibold text-base">{{ __('SEO') }}</h3>
                    </div>
                    <div class="p-4 space-y-3">
                        <div>
                            <label class="text-xs uppercase font-semibold text-gray-500">{{ __('Meta Title') }}</label>
                            <input type="text" name="meta_title" value="{{ $item->meta_title ?? old('meta_title') }}" class="form-input mt-1 w-full">
                        </div>
                        <div>
                            <label class="text-xs uppercase font-semibold text-gray-500">{{ __('Meta Keywords') }}</label>
                            <input type="text" name="meta_keywords" value="{{ $item->meta_keywords ?? old('meta_keywords') }}" class="form-input mt-1 w-full">
                        </div>
                        <div>
                            <label class="text-xs uppercase font-semibold text-gray-500">{{ __('Meta Description') }}</label>
                            <textarea name="meta_description" rows="3" class="form-textarea mt-1 w-full">{{ $item->meta_description ?? old('meta_description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="btn btn-primary">{{ __('Save Post') }}</button>
        </div>
    </form>

    <style>
        .markdown-editor .EasyMDEContainer .CodeMirror {
            min-height: 420px;
        }
    </style>
    <script>
        (() => {
            const textarea = document.getElementById('post-content-editor');
            const form = document.getElementById('post-form');

            if (!textarea || !form || typeof EasyMDE === 'undefined') {
                return;
            }

            const easyMDE = new EasyMDE({
                element: textarea,
                forceSync: true,
            });

            form.addEventListener('submit', function (event) {
                textarea.value = easyMDE.value();
            });
        })();
    </script>
</x-layout.default>
