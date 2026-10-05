<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

/**
 * Server-side validation for the login form.
 *
 * The browser's Zod schema mirrors these rules for UX only — this is the
 * authority.
 */
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'max:255'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Deliberately vague: never reveal whether an email exists.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Please enter your email address.',
            'password.required' => 'Please enter your password.',
        ];
    }

    /**
     * Throttle per email+IP so one account cannot be brute-forced and a
     * single attacker cannot lock out many accounts.
     */
    public function ensureIsNotRateLimited(): void
    {
        $key = 'login:'.mb_strtolower((string) $this->input('email')).'|'.$this->ip();

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }
    }

    public function recordLoginAttempt(): void
    {
        $key = 'login:'.mb_strtolower((string) $this->input('email')).'|'.$this->ip();

        \Illuminate\Support\Facades\RateLimiter::hit($key);
    }

    public function clearLoginAttempts(): void
    {
        $key = 'login:'.mb_strtolower((string) $this->input('email')).'|'.$this->ip();

        \Illuminate\Support\Facades\RateLimiter::clear($key);
    }
}