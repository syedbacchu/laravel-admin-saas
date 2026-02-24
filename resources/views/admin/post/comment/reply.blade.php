<x-layout.default>
    @section('title', $pageTitle)

    <div class="panel mt-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                {{ $pageTitle }}
            </h1>
            <a href="{{ route('postComment.list') }}" class="btn btn-outline-primary">{{ __('Back') }}</a>
        </div>

        <div class="mb-6 border rounded-lg p-4 bg-gray-50">
            <p class="text-sm text-gray-500 mb-1">{{ __('Post') }}</p>
            <p class="font-semibold">{{ $item->post?->title ?? '-' }}</p>

            <p class="text-sm text-gray-500 mt-4 mb-1">{{ __('Original Comment') }}</p>
            <p class="mb-2">{{ $item->comment }}</p>

            <p class="text-sm text-gray-500">
                {{ __('By') }}: {{ $item->user?->name ?: ($item->name ?: 'Guest') }}
            </p>
        </div>

        <form method="POST" action="{{ route('postComment.replyStore', $item->id) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">{{ __('Reply') }}</label>
                <textarea
                    name="comment"
                    rows="6"
                    class="form-textarea w-full"
                    required
                >{{ old('comment') }}</textarea>
            </div>

            <button type="submit" class="btn btn-secondary">
                {{ __('Submit Reply') }}
            </button>
        </form>
    </div>
</x-layout.default>
