<x-layout.default>
    @section('title', $pageTitle)

    <div class="panel mt-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">{{ $pageTitle }}</h1>

        <form method="POST" action="{{ route('tenant.store') }}">
            @csrf
            @if(isset($item))
                <input type="hidden" name="edit_id" value="{{ $item->id }}">
            @endif

            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-4">
                <h5 class="text-xl font-semi-bold text-gray-600 dark:text-gray-100">{{ __('Company Information') }}</h5>
                <div>
                    <button type="submit" class="btn btn-secondary">{{ __('Submit') }}</button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="mb-2">
                    <label for="company_name">{{ __('Company Name') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input name="company_name" type="text" value="{{ old('company_name', $item->company_name ?? '') }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                    </div>
                </div>

                <div class="mb-2">
                    <label for="company_username">{{ __('Company Username') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input name="company_username" type="text" value="{{ old('company_username', $item->company_username ?? '') }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" placeholder="rifatmotor" />
                    </div>
                    <small class="text-gray-500">{{ __('Used in URL like carinfo.com/company_username') }}</small>
                </div>
            </div>

            <h5 class="text-xl font-semi-bold text-gray-600 dark:text-gray-100 mt-6 mb-4">{{ __('Owner Information') }}</h5>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="mb-2">
                    <label for="owner_name">{{ __('Owner Name') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input name="owner_name" type="text" value="{{ old('owner_name', $item->owner_name ?? '') }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                    </div>
                </div>

                <div class="mb-2">
                    <label for="owner_email">{{ __('Owner Email') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input name="owner_email" type="email" value="{{ old('owner_email', $item->owner_email ?? '') }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                    </div>
                </div>

                <div class="mb-2">
                    <label for="owner_phone">{{ __('Owner Phone') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input name="owner_phone" type="text" value="{{ old('owner_phone', $item->owner_phone ?? '') }}" class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                    </div>
                </div>

                <div class="mb-2">
                    <label for="owner_password">{{ __('Owner Password') }}</label>
                    <div class="flex">
                        {!! defaultInputIcon() !!}
                        <input name="owner_password" type="text" class="form-input ltr:rounded-l-none rtl:rounded-r-none" />
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-layout.default>
