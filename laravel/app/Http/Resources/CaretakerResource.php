<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Caretaker;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Caretaker
 */
class CaretakerResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'name' => (string) $this->name,
            'email' => (string) $this->email,
            'phone' => $this->phone,
            'id_number' => $this->id_number,
            'avatar' => $this->avatar,

            // Parsed from the CSV column into a real list, so the client never
            // has to split a string.
            'assigned_properties' => $this->assignedPropertyIds(),

            'assigned_count' => count($this->assignedPropertyIds()),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}