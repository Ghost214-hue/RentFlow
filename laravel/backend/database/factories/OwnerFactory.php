<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Owner;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Owner>
 */
class OwnerFactory extends Factory
{
    protected $model = Owner::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('07########'),
            // 'password' is cast to 'hashed' on the model, so a plain string here
            // is hashed automatically on create.
            'password' => 'password',
        ];
    }

    public function withPassword(string $plain): static
    {
        return $this->state(fn (): array => ['password' => Hash::make($plain)]);
    }
}