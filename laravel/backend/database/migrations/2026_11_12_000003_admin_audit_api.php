<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- 5. Admin platform ---------------------------------------------------
        if (! Schema::hasTable('admins')) {
            Schema::create('admins', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('status')->default('active');
                $table->string('two_factor_secret', 32)->nullable();
                $table->string('two_factor_recovery', 32)->nullable();
                $table->boolean('two_factor_required')->default(true);
                $table->string('admin_role')->default('super_admin');
                $table->timestamps();
            });
        }

        // --- 6. Audit trail ------------------------------------------------------
        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->string('actor_type')->comment('admin | owner | caretaker | renter');
                $table->unsignedBigInteger('actor_id')->nullable();
                $table->unsignedBigInteger('target_owner_id')->nullable();
                $table->string('action');
                $table->string('subject_type')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->text('reason')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 1024)->nullable();
                $table->timestamps();

                $table->index(['actor_type', 'actor_id']);
                $table->index('target_owner_id');
                $table->index('created_at');
            });
        }

        if (! Schema::hasTable('support_access_grants')) {
            Schema::create('support_access_grants', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('owner_id');
                $table->unsignedBigInteger('admin_id');
                $table->string('scope')->comment('names | phones | id_numbers | payment_lines');
                $table->text('reason');
                $table->dateTime('expires_at')->nullable();
                $table->dateTime('revoked_at')->nullable();
                $table->timestamps();

                $table->foreign('owner_id')->references('id')->on('owners')->onDelete('cascade');
                $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
                $table->unique(['owner_id', 'admin_id']);
            });
        }

        // --- 7. API idempotency ---------------------------------------------------
        if (! Schema::hasTable('idempotency_keys')) {
            Schema::create('idempotency_keys', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('owner_id');
                $table->string('key', 128)->unique();
                $table->string('request_hash', 128);
                $table->text('response')->nullable();
                $table->dateTime('expires_at')->nullable();
                $table->timestamps();

                $table->foreign('owner_id')->references('id')->on('owners')->onDelete('cascade');
            });
        }

        // --- 8. CaretakerPermissions: owner-granted overrides ---------------------
        if (! Schema::hasTable('caretaker_permissions')) {
            Schema::create('caretaker_permissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('caretaker_id');
                $table->unsignedBigInteger('owner_id');
                $table->string('ability')->comment('payments.record | payments.reverse | bills.generate | ...');
                $table->boolean('granted')->default(true);
                $table->text('reason')->nullable();
                $table->timestamps();

                $table->foreign('caretaker_id')->references('id')->on('caretakers')->onDelete('cascade');
                $table->foreign('owner_id')->references('id')->on('owners')->onDelete('cascade');
                $table->unique(['caretaker_id', 'ability']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('caretaker_permissions');
        Schema::dropIfExists('idempotency_keys');
        Schema::dropIfExists('support_access_grants');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('admins');
    }
};
