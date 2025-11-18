<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 
                            'string',
                        function ($attribute, $value, $fail) {
                            // Se não é email, valida username
                            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                // Username só pode ter letras, números, underscore e hífen
                                if (!preg_match('/^[a-zA-Z0-9_-]+$/', $value)) {
                                    $fail('The username can only contain letters, numbers, underscores (_), and hyphens (-).');
                                }
                                
                                // Username deve ter pelo menos 3 caracteres
                                if (strlen($value) < 3) {
                                    $fail('The username must be at least 3 characters long.');
                                }
                            }
                        },
                ],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Determina se o input é email ou username
        $credentials = $this->getCredentials();

         // Primeiro tenta sem o status
        $credentialsWithoutStatus = $credentials;
        unset($credentialsWithoutStatus['status']);
        
        $user = \App\Models\User::where(array_key_first($credentialsWithoutStatus), $credentialsWithoutStatus[array_key_first($credentialsWithoutStatus)])->first();
        
        // Se usuário existe mas está inativo
        if ($user && $user->status !== 'active') {
            throw ValidationException::withMessages([
                'username' => 'Your account is inactive. Please contact support.',
            ]);
        }

        if (!Auth::attempt($credentials, $this->boolean('remember'))) {
            \Log::info('Login falhou para: ' . $this->input('username'));
            
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'username' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Get the credentials for authentication.
     * Detects if input is email or username.
     *
     * @return array
     */
    protected function getCredentials(): array
    {
        $login = $this->input('username');
        
        // Detecta se é email usando filter_var
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        return [
            $fieldType => $login,
            'password' => $this->input('password'),
            'status' => 'active',
        ];
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('username')).'|'.$this->ip());
    }
}