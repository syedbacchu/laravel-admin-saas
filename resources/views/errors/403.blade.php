<x-layout.auth>
    <div class="flex justify-center items-center min-h-screen bg-gradient-to-t from-[#ffd9d9] to-[#fff5f5]">
        <div class="text-center p-5 font-semibold">
            <div class="flex justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-red-600 mb-2">{{ __('403 - Permission Denied') }}</h1>
            <p class="text-black mb-6">
                {{ __("You don’t have permission to access this page.") }}<br>
                {{ __('Please contact your administrator if you think this is a mistake.') }}
            </p>

            <a href="{{ route('dashboard') }}"
                class="inline-block px-6 py-2.5 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition">
                {{ __('Go To Dashboard') }}
            </a>
        </div>
    </div>
</x-layout.auth>
