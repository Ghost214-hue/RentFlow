<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create admin credentials seeder table (one-time setup)
        // This migration creates a table to store initial admin credentials
        // that are used for the first admin login before the users table is populated

        Schema::create('admin_initial_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password_hash');
            $table->string('temp_token')->unique()->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('activated')->default(false);
            $table->timestamps();

            $table->index('email');
            $table->index('temp_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_initial_credentials');
    }
};