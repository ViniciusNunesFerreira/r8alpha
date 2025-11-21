<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateDepositRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $minUsd = config('payment.deposit_limits.min_usd');
        $maxUsd = config('payment.deposit_limits.max_usd');

        return [
            'amount' => [
                'required',
                'numeric',
                "min:{$minUsd}",
                function ($attribute, $value, $fail) use ($maxUsd) {
                    if ($maxUsd && $value > $maxUsd) {
                        $fail("The maximum deposit amount is $" . number_format($maxUsd, 2, '.', ','));
                    }
                },
                
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
            'payment_method' => [
                'required',
                'in:pix,crypto',
                function ($attribute, $value, $fail) {
                    // Verifica se o método está habilitado
                    if ($value === 'pix' && !config('payment.pix.enabled')) {
                        $fail('PIX is temporarily unavailable.');
                    }
                    if ($value === 'crypto' && !config('payment.nowpayments.enabled')) {
                        $fail('Cryptocurrency payments are temporarily unavailable.');
                    }
                },
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        $minUsd = config('payment.deposit_limits.min_usd');

        return [
            'amount.required' => 'The deposit amount is mandatory.',
            'amount.numeric' => 'The value must be a valid number.',
            'amount.min' => "The minimum deposit amount is $" . number_format($minUsd, 2, '.', ','),
            'amount.regex' => 'The value must have a maximum of 2 decimal places..',
            'payment_method.required' => 'Select a payment method..',
            'payment_method.in' => 'Invalid payment method.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'amount' => 'value',
            'payment_method' => 'payment method',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Remove caracteres não numéricos (exceto ponto)
        if ($this->has('amount')) {
            $amount = $this->input('amount');
            $amount = preg_replace('/[^0-9.]/', '', $amount);
            $this->merge(['amount' => $amount]);
        }
    }
}