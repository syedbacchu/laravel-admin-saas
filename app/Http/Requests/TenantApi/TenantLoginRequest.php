<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TenantLoginRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'login' => strtolower(trim((string) $this->input('login'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:180'],
            'password' => ['required', 'string'],
        ];
    }

    public function ensureIsNotRateLimited(string $companyUsername): void
    {
        $maxAttempts = 4;

        if (!RateLimiter::tooManyAttempts($this->throttleKey($companyUsername), $maxAttempts)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($companyUsername));

        throw ValidationException::withMessages([
            'login' => [trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
                'time' => $this->formatThrottleDuration($seconds),
            ])],
        ]);
    }

    public function throttleKey(string $companyUsername): string
    {
        return Str::transliterate(
            Str::lower($companyUsername . '|' . $this->input('login')) . '|' . $this->ip()
        );
    }

    protected function formatThrottleDuration(int $seconds): string
    {
        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes <= 0) {
            return $remainingSeconds . ' second' . ($remainingSeconds === 1 ? '' : 's');
        }

        if ($remainingSeconds <= 0) {
            return $minutes . ' min';
        }

        return $minutes . ' min ' . $remainingSeconds . ' second' . ($remainingSeconds === 1 ? '' : 's');
    }
}
