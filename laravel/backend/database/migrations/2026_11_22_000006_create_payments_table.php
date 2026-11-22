<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Create the `payments` table (received payments).
     *
     * NOTE: The Payment model sets $timestamps = false, so this table has
     * created_at only — no updated_at column.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('owner_id')->index();
            $table->unsignedInteger('tenant_id');
            $table->unsignedInteger('house_id')->nullable();
            $table->string('month', 7)->nullable()->comment('YYYY-MM format for billing period');
            $table->decimal('amount', 12, 2);
            $table->enum('type', ['Rent', 'Water', 'Electricity', 'Deposit', 'Mixed'])->default('Rent');
            $table->string('method', 50)->default('M-Pesa');
            $table->date('date')->nullable();
            $table->enum('status', ['completed', 'pending', 'failed', 'reversed'])->default('completed');
            $table->boolean('tenant_confirmed')->default(0);
            $table->datetime('confirmed_at')->nullable();
            $table->datetime('reversed_at')->nullable();
            $table->string('reversed_reason', 255)->nullable();
            $table->string('receipt', 100)->nullable();
            $table->string('reference', 100)->nullable();
            $table->string('description', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
