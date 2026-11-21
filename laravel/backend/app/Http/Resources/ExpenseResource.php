<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Expense
 */
class ExpenseResource extends JsonResource
{
    /**
     * Money is a STRING, never a float.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'amount' => (string) $this->amount,
            'category' => (string) $this->category,
            'description' => $this->description,
            'date' => $this->date?->toDateString(),
            'paid_to' => $this->paid_to,
            'receipt' => $this->receipt,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
