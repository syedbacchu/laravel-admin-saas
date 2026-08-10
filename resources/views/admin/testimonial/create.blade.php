<x-layout.default>
    @section('title', $pageTitle)

    <form
        id="testimonial-form"
        method="POST"
        action="{{ route('testimonial.store') }}"
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
                <a href="{{ route('testimonial.list') }}" class="btn btn-outline-primary">{{ __('Back') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('Save Testimonial') }}</button>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">
                <div class="panel !p-0 overflow-hidden border border-[#e0e6ed] dark:border-[#1b2e4b]">
                    <div class="px-4 py-3 border-b border-[#e0e6ed] dark:border-[#1b2e4b] bg-[#fafafa] dark:bg-[#0b1320]">
                        <h3 class="font-semibold text-base">{{ __('Testimonial Information') }}</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        <input
                            name="name"
                            data-slug-source
                            type="text"
                            value="{{ $item->name ?? old('name') }}"
                            class="form-input text-lg font-semibold"
                            placeholder="{{ __('Add name') }}"
                            required
                        />

                        <div>
                            <label class="text-xs uppercase font-semibold text-gray-500">{{ __('Review Text') }}</label>
                            <input type="text" name="review_text" value="{{ $item->review_text ?? old('review_text') }}" class="form-input mt-1 w-full">
                        </div>

                        <div>
                            <label class="text-xs uppercase font-semibold text-gray-500">
                                {{ __('Review Star') }}
                            </label>

                            <div class="flex gap-2 mt-2">
                                @for ($i = 5; $i >= 1; $i--)
                                    <label class="flex items-center gap-1">
                                        <input type="radio" name="review_star" value="{{ $i }}"
                                            @checked(($item->review_star ?? old('review_star')) == $i)>
                                        {{ $i }} ⭐
                                    </label>
                                @endfor
                            </div>
                        </div>

                        <div>
                            <label class="text-xs uppercase font-semibold text-gray-500">{{ __('Designation') }}</label>
                            <input type="text" name="designation" value="{{ $item->designation ?? old('designation') }}" class="form-input mt-1 w-full">
                        </div>
                    </div>
                    {{-- Custom Fields --}}
                    @if(isset($item))
                        @customFields($item)
                    @else
                        @customFields(\App\Models\Testimonial::class)
                    @endif
                </div>
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
                                <option value="1" @selected(($item->status ?? 1) == 1)>Active</option>
                                <option value="0" @selected(($item->status ?? 1) == 0)>Inactive</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs uppercase font-semibold text-gray-500">{{ __('Sort Order') }}</label>
                                <input type="number" name="sort_order" value="{{ $item->sort_order ?? old('sort_order', 0) }}" class="form-input mt-1 w-full" />
                            </div>

                        </div>
                    </div>
                </div>

                <div class="panel !p-0 overflow-hidden border border-[#e0e6ed] dark:border-[#1b2e4b]">
                    <div class="px-4 py-3 border-b border-[#e0e6ed] dark:border-[#1b2e4b] bg-[#fafafa] dark:bg-[#0b1320]">
                        <h3 class="font-semibold text-base">{{ __('Images') }}</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        <div x-data="fileManager('{{ $item->image ?? old('image', '') }}', 'image')" class="space-y-2">
                            <label class="text-xs uppercase font-semibold text-gray-500">{{ __('image') }}</label>
                            <button type="button"
                                    x-on:click="$dispatch('open-file-manager', { callback: callbackName })"
                                    class="btn btn-outline-primary btn-sm w-full">
                                {{ __('Choose image') }}
                            </button>
                            <input type="hidden" name="image" x-model="fileUrl">
                            <template x-if="filePreview">
                                <img :src="filePreview" class="rounded-lg border object-cover w-full max-h-[160px]">
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="btn btn-primary">{{ __('Save Testimonial') }}</button>
        </div>
    </form>
</x-layout.default>
