<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminLoginRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:180'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'login.required' => __('Please enter your email, username, or phone number.'),
            'password.required' => __('Password is required.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'login' => strtolower(trim($this->login)),
        ]);
    }

    /**
     * Ensure that the user is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        $maxAttempts = 4;
        $decaySeconds = 20 * 60;

        if (!RateLimiter::tooManyAttempts($this->throttleKey(), $maxAttempts)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Generate a unique key for rate limiting.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('login')) . '|' . $this->ip());
    }

    /**
     * Record a failed login attempt.
     */
    public function hitRateLimiter(): void
    {
        RateLimiter::hit($this->throttleKey(), 20 * 60);
    }

    /**
     * Clear the rate limiter on successful login.
     */
    public function clearRateLimiter(): void
    {
        RateLimiter::clear($this->throttleKey());
    }
}
