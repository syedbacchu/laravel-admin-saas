<x-layout.default>
@section('title', $pageTitle)

<div class="panel mt-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">
        {{ $pageTitle }}
    </h1>

    <div>
        <form method="POST" action="{{ route('pages.update', ['id' => $page->id]) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Page Name') }} <span class="text-red-500">*</span></label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input type="text" name="name" value="{{ old('name', $page->name) }}"
                            class="form-input w-full" required placeholder="{{ __('Enter page name') }}">
                    </div>
                    @error('name')
                        <div class="text-red-500 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Slug -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Slug') }} <span class="text-red-500">*</span></label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input type="text" name="slug" value="{{ old('slug', $page->slug) }}"
                            class="form-input w-full" required placeholder="{{ __('Enter slug') }}">
                    </div>
                    <small class="text-gray-500">{{ __('Auto-generated from name if left blank') }}</small>
                    @error('slug')
                        <div class="text-red-500 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Meta Title -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Meta Title') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}"
                            class="form-input w-full" placeholder="{{ __('Enter meta title') }}">
                    </div>
                    @error('meta_title')
                        <div class="text-red-500 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Meta Keywords -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Meta Keywords') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input type="text" name="meta_keyword" value="{{ old('meta_keyword', $page->meta_keyword) }}"
                            class="form-input w-full" placeholder="{{ __('Enter meta keywords') }}">
                    </div>
                    @error('meta_keyword')
                        <div class="text-red-500 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Meta Description -->
                <div class="mb-4 md:col-span-2">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Meta Description') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <textarea name="meta_description" class="form-input w-full" rows="3"
                            placeholder="{{ __('Enter meta description') }}">{{ old('meta_description', $page->meta_description) }}</textarea>
                    </div>
                    @error('meta_description')
                        <div class="text-red-500 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Meta Image -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Meta Image URL') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input type="text" name="meta_image" value="{{ old('meta_image', $page->meta_image) }}"
                            class="form-input w-full" placeholder="{{ __('Enter meta image URL') }}">
                    </div>
                    @error('meta_image')
                        <div class="text-red-500 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Status -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Status') }}</label>
                    <div class="flex items-center">
                        <input type="checkbox" name="status" value="1" {{ old('status', $page->status) ? 'checked' : '' }}
                            class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label class="ml-2 text-gray-700">{{ __('Active') }}</label>
                    </div>
                    @error('status')
                        <div class="text-red-500 mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('pages.sections.index', ['pageId' => $page->id]) }}" class="px-6 py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    {{ __('Manage Sections') }}
                </a>
                <a href="{{ route('pages.index') }}" class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-lg hover:from-indigo-700 hover:to-blue-700 transition-all">
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('Update Page') }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const nameInput = document.querySelector('input[name="name"]');
        const slugInput = document.querySelector('input[name="slug"]');

        nameInput.addEventListener('input', function() {
            const name = this.value;
            const slug = name.toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
            slugInput.value = slug;
        });
    });
</script>
@endpush
</x-layout.default>