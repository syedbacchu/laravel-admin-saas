<x-layout.default>
    @section('title', $pageTitle)

    <div class="panel mt-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">{{ $pageTitle }}</h1>

        <form method="POST" action="{{ route('tenant.update', $item->id) }}">
            @csrf
            @method('PUT')

            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-4">
                <h5 class="text-xl font-semi-bold text-gray-600 dark:text-gray-100">{{ __('Edit Owner Information')}}</h5>
                <div>
                    <button type="submit" class="btn btn-secondary">{{ __('Update') }}</button>
                    <a href="{{ route('tenant.list') }}" class="btn btn-outline-secondary ms-2">{{ __('Cancel') }}</a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="mb-2">
                    <label for="company_name">{{ __('Company Name') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input type="text" value="{{ e($item->company_name) }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" disabled />
                    </div>
                    <small class="text-gray-500">{{ __('Company name cannot be changed') }}</small>
                </div>

                <div class="mb-2">
                    <label for="company_username">{{ __('Company Username') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input type="text" value="{{ e($item->company_username) }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" disabled />
                    </div>
                    <small class="text-gray-500">{{ __('Company username cannot be changed') }}</small>
                </div>

                <div class="mb-2">
                    <label for="owner_name">{{ __('Owner Name') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input name="owner_name" type="text" value="{{ old('owner_name', $item->owner->name ?? '') }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" required />
                    </div>
                    @error('owner_name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-2">
                    <label for="owner_email">{{ __('Owner Email') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input name="owner_email" type="email" value="{{ old('owner_email', $item->owner->email ?? '') }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                    </div>
                    @error('owner_email')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-2">
                    <label for="owner_phone">{{ __('Owner Phone') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input name="owner_phone" type="text" value="{{ old('owner_phone', $item->owner->phone ?? '') }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                    </div>
                    @error('owner_phone')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-2">
                    <label for="owner_password">{{ __('Owner Password') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input name="owner_password" type="password" class="form-input ltr:rounded-l-none rtl:rounded-r-none" placeholder="{{ __('Leave blank to keep current password') }}" />
                    </div>
                    @error('owner_password')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                    <small class="text-gray-500">{{ __('Minimum 8 characters. Leave blank to keep current password') }}</small>
                </div>
            </div>
        </form>
    </div>
</x-layout.default>
