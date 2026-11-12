<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EmailLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailLog>
 *
 * to_name and sent_at are NOT NULL in the live schema, and there is no
 * updated_at column.
 */
class EmailLogFactory extends Factory
{
    protected $model = EmailLog::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'to_email' => fake()->safeEmail(),
            'to_name' => fake()->name(),
            'subject' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'status' => 'sent',
            'sent_at' => now(),
        ];
    }

    public function failed(string $error = 'SMTP connection refused'): static
    {
        return $this->state(fn (): array => ['status' => 'failed', 'error' => $error]);
    }
}