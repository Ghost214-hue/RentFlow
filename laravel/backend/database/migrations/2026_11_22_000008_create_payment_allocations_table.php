<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Create the `payment_allocations` table (payment → bill_item links).
     *
     * NOTE: The PaymentAllocation model sets UPDATED_AT = null, so this table
     * has created_at only — no updated_at column.
     */
    public function up(): void
    {
        Schema::create('payment_allocations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('owner_id')->index();
            $table->unsignedInteger('payment_id');
            $table->unsignedInteger('bill_item_id');
            $table->string('category', 50);
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
    }
};
