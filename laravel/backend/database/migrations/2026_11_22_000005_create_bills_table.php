<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Create the `bills` table (monthly invoices). */
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('owner_id')->index();
            $table->unsignedInteger('house_id');
            $table->unsignedInteger('tenant_id')->nullable();
            $table->string('month', 7)->comment('YYYY-MM format');
            $table->string('type', 20)->default('Rent');
            $table->string('description', 255)->nullable();
            $table->decimal('rent', 12, 2)->default(0.00);
            $table->decimal('water', 12, 2)->default(0.00);
            $table->decimal('electricity', 12, 2)->default(0.00);
            $table->decimal('total', 12, 2)->default(0.00);
            $table->enum('status', ['paid', 'partial', 'pending', 'overdue'])->default('pending');
            $table->date('due_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
