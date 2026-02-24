<x-layout.auth pageTitle="{{$pageTitle ?? 'Title Here'}}">
    <div class="flex min-h-screen">
        <!-- Left side cover -->
        <div
            class="bg-gradient-to-t from-[#ff1361bf] to-[#44107A] w-1/2 min-h-screen hidden lg:flex flex-col items-center justify-center text-white dark:text-black p-4">
            <div class="w-full mx-auto mb-5">
                <img src="/assets/images/auth-cover.svg" alt="coming_soon"
                     class="lg:max-w-[370px] xl:max-w-[500px] mx-auto" />
            </div>
            <h3 class="text-3xl font-bold mb-4 text-center">
                {{__('Join the community of expert developers')}}
            </h3>
            <p>{{__('It is easy to setup with great customer experience. Start your 7-day free trial')}}</p>
        </div>

        <!-- Right side form -->
        <div class="w-full lg:w-1/2 flex justify-center items-center">
            <div class="max-w-[480px] w-full p-5 md:p-10 flex flex-col justify-center min-h-[500px]">

                <!-- 🔹 Logo -->
                <div class="text-center mb-8">
                    <img src="{{ asset('assets/images/logo.png') }}"
                         alt="Logo"
                         class="w-32 lg:w-64 h-auto mx-auto drop-shadow-md dark:brightness-110" />
                </div>

                <!-- 🔹 Title -->
                <h2 class="font-bold text-3xl mb-3 text-center">{{__('Reset Password')}}</h2>
                <p class="mb-7 text-center text-white-dark">
                    {{__('Using strong password reset your password')}}
                </p>

                <!-- 🔹 Login Form -->
                <form class="space-y-5" method="POST" action="{{ route('auth.forgot.password.resetProcess') }}">
                    @csrf
                    <input name="password_token" type="hidden" value="{{$auth_token}}">
                    <div>
                        <label for="otp">{{ __('Otp') }}</label>
                        <input
                            name="otp"
                            id="otp"
                            type="text"
                            class="form-input w-full"
                            value="{{ old('otp') }}"
                        />
                    </div>
                    <div>
                        <label for="password">{{ __('Password') }}</label>
                        <input
                            name="password"
                            id="password"
                            type="password"
                            class="form-input w-full"
                        />
                    </div>
                    <div>
                        <label for="confirm_password">{{ __('Retype Password') }}</label>
                        <input
                            name="confirm_password"
                            id="confirm_password"
                            type="password"
                            class="form-input w-full"
                        />
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <a href="{{route('login')}}"
                           class="text-secondary hover:underline font-medium">
                            {{__('')}}
                        </a>
                        <a href="{{route('login')}}"
                           class="text-primary hover:underline font-medium">
                            {{__('Login')}}
                        </a>
                    </div>

                    <button type="submit" class="btn btn-primary w-full">{{__('Reset')}}</button>
                </form>

                <div class="mt-10 mb-5"></div> <!-- spacing -->
            </div>
        </div>
    </div>
</x-layout.auth>
