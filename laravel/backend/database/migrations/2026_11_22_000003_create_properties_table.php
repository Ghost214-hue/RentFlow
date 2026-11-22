<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Create the `properties` table (landlord buildings). */
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('owner_id')->index();
            $table->string('name');
            $table->text('address');
            $table->string('type')->default('Apartment Block');
            $table->unsignedInteger('units')->default(0);
            $table->unsignedInteger('occupied')->default(0);
            $table->string('image', 500)->nullable();
            $table->unsignedInteger('caretaker_id')->nullable();
            $table->decimal('rent', 12, 2)->default(0.00);
            $table->enum('payment_method_type', ['paybill', 'till', 'bank', 'mobile_money'])->nullable();
            $table->string('paybill_number', 50)->nullable();
            $table->string('paybill_account', 100)->nullable();
            $table->string('till_number', 50)->nullable();
            $table->string('bank_name', 255)->nullable();
            $table->string('bank_account', 100)->nullable();
            $table->string('bank_branch', 255)->nullable();
            $table->string('mobile_money_number', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
