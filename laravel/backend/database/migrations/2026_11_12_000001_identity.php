<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- 1. Identity: users table -------------------------------------------
        // One login can hold several profiles. `users.email` is globally unique.
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique()->comment('Globally unique login identity');
            $table->string('password');
            $table->string('status')->default('active')->comment('active | suspended | terminated');
            $table->timestamp('last_login')->nullable();
            $table->string('two_factor_secret', 32)->nullable()->comment('TOTP secret for admin/2FA');
            $table->string('two_factor_recovery', 32)->nullable()->comment('Recovery codes');
            $table->boolean('two_factor_required')->default(true)->comment('Mandatory TOTP for admins');
            $table->timestamps();

            $table->index('email');
        });

        // --- 2. Identity: nullable user_id on existing actor tables --------------
        $this->addUserIdTo('owners');
        $this->addUserIdTo('caretakers');
        $this->addUserIdTo('tenants');
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        $this->dropUserIdFrom('tenants');
        $this->dropUserIdFrom('caretakers');
        $this->dropUserIdFrom('owners');
    }

    private function addUserIdTo(string $table): void
    {
        if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'user_id')) {
            Schema::table($table, function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->index('user_id');
            });
        }
    }

    private function dropUserIdFrom(string $table): void
    {
        if (Schema::hasTable($table) && Schema::hasColumn($table, 'user_id')) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropIndex([$table->getTable() . '_user_id_index']);
                $table->dropColumn('user_id');
            });
        }
    }
};
