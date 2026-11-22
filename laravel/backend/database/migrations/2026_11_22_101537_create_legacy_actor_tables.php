<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the legacy actor tables (owners, caretakers, tenants) that were
     * never ported to migrations. The existing migration `2026_11_12_000001_identity`
     * adds `user_id` to these tables and guards with Schema::hasTable(), so it
     * will no-op correctly if they already exist.
     */
    public function up(): void
    {
        Schema::create('owners', function (Blueprint $table) {
            $table->integer('id')->unsigned()->autoIncrement();
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->string('status')->default('active');
            $table->string('phone', 50)->nullable();
            $table->string('avatar', 10)->default('OW');
            $table->datetime('last_login')->nullable();
            $table->timestamps();

            $table->index('email');
        });

        Schema::create('caretakers', function (Blueprint $table) {
            $table->integer('id')->unsigned()->autoIncrement();
            $table->integer('owner_id')->unsigned();
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('phone', 50)->nullable();
            $table->string('id_number', 100)->nullable();
            $table->string('password', 255);
            $table->string('avatar', 10)->default('CT');
            $table->text('assigned_properties')->nullable();
            $table->timestamps();

            $table->index('email');
            $table->index('owner_id');
        });

        Schema::create('tenants', function (Blueprint $table) {
            $table->integer('id')->unsigned()->autoIncrement();
            $table->integer('owner_id')->unsigned();
            $table->integer('property_id')->unsigned()->nullable();
            $table->integer('house_id')->unsigned()->nullable();
            $table->string('name', 255);
            $table->string('email', 255)->nullable()->unique();
            $table->string('profile_picture', 500)->nullable();
            $table->string('password', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('id_number', 100)->nullable();
            $table->string('next_of_kin_name', 255)->nullable();
            $table->string('next_of_kin_phone', 50)->nullable();
            $table->string('next_of_kin_email', 255)->nullable();
            $table->string('id_type', 50)->default('National ID');
            $table->date('lease_start')->nullable();
            $table->date('lease_end')->nullable();
            $table->decimal('deposit', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->decimal('credit', 12, 2)->default(0);
            $table->decimal('water_balance', 12, 2)->nullable();
            $table->decimal('elec_balance', 12, 2)->nullable();
            $table->enum('status', ['active', 'pending_termination', 'terminated'])->default('active');
            $table->text('documents')->nullable();
            $table->timestamps();

            $table->index('email');
            $table->index('owner_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
        Schema::dropIfExists('caretakers');
        Schema::dropIfExists('owners');
    }
};
