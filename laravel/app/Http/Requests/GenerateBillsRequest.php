<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Bill;
use Illuminate\Foundation\Http\FormRequest;

/** Validation for generating the current month's bills. */
class GenerateBillsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Bill::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'month' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'due_date' => ['nullable', 'date'],
            'property_id' => ['nullable', 'integer'],
            // Utility charges, keyed by house id (water_<id>) or elec_<id>.
            'utilities' => ['nullable', 'array'],
        ];
    }

    /**
     * Split the utility array into water/electricity maps keyed by house id.
     *
     * @return array<string, string>
     */
    public function utilityCharges(): array
    {
        $charges = [];

        foreach ((array) $this->input('utilities', []) as $key => $amount) {
            if (is_string($key) && is_numeric($amount) && (float) $amount > 0) {
                $charges[$key] = (string) $amount;
            }
        }

        return $charges;
    }
}