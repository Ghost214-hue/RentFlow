<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('caretaker_permissions') && ! Schema::hasColumn('caretaker_permissions', 'expires_at')) {
            Schema::table('caretaker_permissions', function (Blueprint $table) {
                $table->timestamp('expires_at')->nullable()->after('reason');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('caretaker_permissions') && Schema::hasColumn('caretaker_permissions', 'expires_at')) {
            Schema::table('caretaker_permissions', function (Blueprint $table) {
                $table->dropColumn('expires_at');
            });
        }
    }
};
