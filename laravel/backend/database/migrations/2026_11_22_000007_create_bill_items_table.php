<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Create the `bill_items` table (charge lines on bills). */
    public function up(): void
    {
        Schema::create('bill_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('owner_id')->index();
            $table->unsignedInteger('bill_id');
            $table->string('type', 50);
            $table->string('description', 255)->nullable();
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->decimal('paid', 12, 2)->default(0.00);
            $table->enum('status', ['pending', 'partial', 'paid'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_items');
    }
};
