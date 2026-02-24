<x-layout.auth>

    <div class="flex justify-center items-center min-h-screen bg-gradient-to-t from-[#c39be3] to-[#f2eafa]">
        <div class="text-center p-5 font-semibold">
            <div class="flex justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18.364 5.636L5.636 18.364M5.636 5.636l12.728 12.728" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-red-600 mb-2">{{ __('Something Went Wrong') }}</h1>
            <p class="text-black mb-6">
                {{ __("We’re sorry, but something unexpected happened.") }}<br>
                {{ __('Please try again later.') }}
            </p>

            <a href="{{ url('/') }}"
                class="inline-block px-6 py-2.5 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition">
                {{ __('Go Back Home') }}
            </a>

            @if(app()->environment('local'))
                <p class="text-xs text-black mt-6">
                    (Debug mode active: {{ app()->environment() }})
                </p>
            @endif
        </div>
    </div>


</x-layout.auth>
