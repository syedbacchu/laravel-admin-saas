<x-layout.default>
    @section('title', $pageTitle)
    <div class="panel mt-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
            <h5 class="text-2xl font-bold text-gray-800">{{ $pageTitle ?? __('Division') }}</h5>

            <a href="{{ route('division.index') }}"
               class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-gray-600 to-gray-700 text-white font-medium rounded-lg shadow-md hover:shadow-lg hover:from-gray-700 hover:to-gray-800 focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{__('Back to List')}}
            </a>
        </div>
        <div>
            <form method="POST" action="{{ $function_type == 'update' ? route('division.update', isset($item) ? $item->id : '') : route('division.store') }}" enctype="multipart/form-data">
                @csrf
                @if($function_type == 'update')
                    @method('PUT')
                @endif
                @if(isset($item))
                    <input type="hidden" name="edit_id" value="{{ $item->id }}">
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="mb-2">
                        <label for="code" class="">{{ __('Code') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input id="code" name="code" type="text" maxlength="4"
                                   @if(isset($item)) value="{{ $item->code }}"
                                   @else value="{{ old('code') }}" @endif
                                   class="form-input ltr:rounded-l-none rtl:rounded-r-none" required />
                        </div>
                        @error('code')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-2">
                        <label for="name" class="">{{ __('Name') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <input id="name" name="name" type="text" maxlength="80"
                                   @if(isset($item)) value="{{ $item->name }}"
                                   @else value="{{ old('name') }}" @endif
                                   class="form-input ltr:rounded-l-none rtl:rounded-r-none" required />
                        </div>
                        @error('name')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="serial" class="block text-gray-700 font-medium mb-2">{{ __('Activation Status') }}</label>
                        <div class="flex">
                            {!! defaultInputIcon() !!}
                            <select name="status" id="" class="form-select">
                                <option value="">{{__('Select')}}</option>
                                @foreach(\App\Enums\StatusEnum::getDeactiveArray() as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        {{ old('status', $item->status ?? '') == $value ? 'selected' : '' }}
                                    >
                                        {{ __($label) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>

                <div>
                    <button type="submit" class="btn btn-secondary mt-6">{{__('Submit')}}</button>
                </div>
            </form>
        </div>
    </div>

</x-layout.default>
