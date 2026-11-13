<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- 3. Caretaker assignments: pivot table ------------------------------
        // Replaces the CSV `caretakers.assigned_properties` with a typed pivot.
        if (! Schema::hasTable('caretaker_property')) {
            Schema::create('caretaker_property', function (Blueprint $table) {
                $table->unsignedBigInteger('caretaker_id');
                $table->unsignedBigInteger('property_id');
                $table->unsignedBigInteger('owner_id');
                $table->unique(['caretaker_id', 'property_id'], 'caretaker_property_unique');
                $table->foreign('caretaker_id')->references('id')->on('caretakers')->onDelete('cascade');
                $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
                $table->foreign('owner_id')->references('id')->on('owners')->onDelete('cascade');
                $table->timestamps();
            });
        }

        // --- 4. Money tables: owner_id for child rows ---------------------------
        $this->addOwnerIdTo('bill_items', 'bill_id');
        $this->addOwnerIdTo('payment_allocations', 'payment_id');
        $this->addOwnerIdTo('password_setup_tokens', 'tokenable_id');
    }

    public function down(): void
    {
        Schema::dropIfExists('caretaker_property');
        $this->dropOwnerIdFrom('password_setup_tokens');
        $this->dropOwnerIdFrom('payment_allocations');
        $this->dropOwnerIdFrom('bill_items');
    }

    private function addOwnerIdTo(string $table, string $parentColumn): void
    {
        if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'owner_id')) {
            Schema::table($table, function (Blueprint $table) use ($parentColumn) {
                $table->unsignedBigInteger('owner_id')->nullable()->after('id');
                $table->index('owner_id');
            });
        }
    }

    private function dropOwnerIdFrom(string $table): void
    {
        if (Schema::hasTable($table) && Schema::hasColumn($table, 'owner_id')) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropIndex([$table->getTable() . '_owner_id_index']);
                $table->dropColumn('owner_id');
            });
        }
    }
};
