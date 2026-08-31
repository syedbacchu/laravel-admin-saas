<x-layout.default>
    @section('title', $pageTitle)

    <div class="panel mt-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">
            {{ $pageTitle }}
        </h1>

        <div>
            <form method="POST" action="{{ $function_type === 'create'
    ? route('component.store')
    : route('component.update', $item->id) }}" enctype="multipart/form-data">
                @csrf
                @if($function_type === 'update')
                    @method('PUT')
                    <input type="hidden" name="edit_id" value="{{ $item->id }}">
                @endif

                <!-- Name -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Component Name') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input type="text" name="name" value="{{ $item->name ?? old('name') }}"
                            class="form-input w-full" required placeholder="{{ __('Enter component name') }}">
                    </div>
                </div>

                <!-- Slug -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Slug') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input type="text" name="slug" value="{{ $item->slug ?? old('slug') }}"
                            class="form-input w-full" required placeholder="{{ __('Enter slug') }}">
                    </div>
                    <small class="text-gray-500">{{ __('Auto-generated from name if left blank') }}</small>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Description') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <textarea name="description" rows="3"
                            class="form-textarea w-full" placeholder="{{ __('Enter description (optional)') }}">{{ $item->description ?? old('description') }}</textarea>
                    </div>
                </div>

                <!-- Status -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">{{ __('Status') }}</label>
                    <div class="flex items-center">
                        <input type="checkbox" name="status" value="1"
                            {{ ($function_type === 'create' ? true : ($item->status ?? old('status'))) ? 'checked' : '' }}
                            class="form-checkbox h-5 w-5 text-indigo-600">
                        <span class="ml-2 text-gray-700">{{ __('Active') }}</span>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end mt-6">
                    <a href="{{ route('component.list') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 mr-3">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-lg hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        {{ $function_type === 'create' ? __('Create Component') : __('Update Component') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-layout.default>
