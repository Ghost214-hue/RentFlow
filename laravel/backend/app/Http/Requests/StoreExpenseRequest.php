<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for recording an outgoing expense (security, caretaker, etc.).
 *
 * Amount is a string to avoid float drift; category is constrained so the
 * reports can group expenses meaningfully.
 */
class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Expense::class) === true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'string', 'numeric', 'min:0.01', 'max:99999999.99'],
            'category' => ['required', Rule::in(['security', 'caretaker', 'maintenance', 'utilities', 'other'])],
            'description' => ['nullable', 'string', 'max:500'],
            'date' => ['required', 'date'],
            'paid_to' => ['nullable', 'string', 'max:255'],
            'receipt' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.numeric' => 'Enter a valid amount, for example 3500.00.',
            'amount.min' => 'Amount must be greater than zero.',
            'category.in' => 'Choose a valid expense category.',
            'date.required' => 'Pick the date the expense was paid.',
        ];
    }

    /**
     * Normalised payload for creating an expense.
     *
     * @return array<string, mixed>
     */
    public function expenseAttributes(): array
    {
        return $this->validated();
    }
}
