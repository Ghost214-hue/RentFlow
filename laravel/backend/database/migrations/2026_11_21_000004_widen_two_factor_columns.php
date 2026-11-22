<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Widen two_factor_secret / two_factor_recovery columns.
 *
 * The original varchar(32) columns are far too small:
 *  - two_factor_secret: Google2FA base32 secret is 32 chars, but future
 *    implementations may use longer secrets.
 *  - two_factor_recovery: hashRecoveryCodes() stores 8 bcrypt hashes (~60
 *    chars each, comma-separated) ≈ 487 chars — this overflows varchar(32)
 *    and causes a 500 "Data too long for column" error when enrolling in 2FA.
 *
 * Both columns are widened to text so recovery codes always fit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table): void {
            $table->text('two_factor_secret')->nullable()->change();
            $table->text('two_factor_recovery')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->text('two_factor_secret')->nullable()->change();
            $table->text('two_factor_recovery')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Reverting would risk truncating stored recovery codes; keep as-is.
        // If you truly need to roll back, widen back to varchar(32) only after
        // clearing all two_factor_secret / two_factor_recovery values.
    }
};