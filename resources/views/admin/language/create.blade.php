<x-layout.default>
    @section('title', $pageTitle)

    <div class="panel mt-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">{{ $pageTitle }}</h1>
        <div>
            <form method="POST" action="{{ isset($item) ? route('language.update', $item->id) : route('language.store') }}">
                @csrf
                @if(isset($item))
                    @method('PUT')
                    <input type="hidden" name="edit_id" value="{{ $item->id }}">
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="mb-2">
                        <label for="name">{{ __('Name') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input name="name" type="text" value="{{ old('name', $item->name ?? '') }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" required />
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="native_name">{{ __('Native Name') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input name="native_name" type="text" value="{{ old('native_name', $item->native_name ?? '') }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="code">{{ __('Code') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input
                                name="code"
                                type="text"
                                value="{{ old('code', $item->code ?? '') }}"
                                class="form-input ltr:rounded-l-none rtl:rounded-r-none"
                                placeholder="en"
                                {{ isset($item) && ((int) $item->is_default === 1 || $item->code === 'en') ? 'readonly' : '' }}
                                required
                            />
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="direction">{{ __('Direction') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            @php $direction = old('direction', $item->direction ?? 'ltr'); @endphp
                            <select name="direction" class="form-select ltr:rounded-l-none rtl:rounded-r-none">
                                <option value="ltr" {{ $direction === 'ltr' ? 'selected' : '' }}>LTR</option>
                                <option value="rtl" {{ $direction === 'rtl' ? 'selected' : '' }}>RTL</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="sort_order">{{ __('Sort Order') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input name="sort_order" type="number" min="0" value="{{ old('sort_order', $item->sort_order ?? 0) }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="status">{{ __('Status') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            @php
                                $defaultLocked = isset($item) && ((int) $item->is_default === 1 || $item->code === 'en');
                                $status = old('status', isset($item) ? (string) $item->status : '0');
                                if ($defaultLocked) {
                                    $status = '1';
                                }
                            @endphp
                            <select name="status" class="form-select ltr:rounded-l-none rtl:rounded-r-none" {{ $defaultLocked ? 'disabled' : '' }}>
                                <option value="1" {{ $status === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ $status === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @if($defaultLocked)
                                <input type="hidden" name="status" value="1">
                            @endif
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit" class="btn btn-secondary mt-6">
                        {{ __('Submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout.default>

