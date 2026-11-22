<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Create the `houses` table (rentable units within a property). */
    public function up(): void
    {
        Schema::create('houses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('owner_id')->index();
            $table->unsignedInteger('property_id');
            $table->string('unit', 50);
            $table->string('type', 100)->default('1 Bedroom');
            $table->enum('status', ['occupied', 'vacant'])->default('vacant');
            $table->unsignedInteger('tenant_id')->nullable();
            $table->decimal('rent', 12, 2)->default(0.00);
            $table->string('water_meter', 100)->nullable();
            $table->string('elec_meter', 100)->nullable();
            $table->decimal('last_reading', 12, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('houses');
    }
};
