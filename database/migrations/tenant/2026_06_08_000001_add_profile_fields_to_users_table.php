<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Encrypted at rest via CipherSweet - must be TEXT to hold ciphertext.
            // Blind indexes for searching live in the separate `blind_indexes` table.
            $table->text('phone')->nullable()->after('email');
            $table->text('street')->nullable()->after('phone');
            $table->text('building_number')->nullable()->after('street');
            $table->text('apartment_number')->nullable()->after('building_number');
            $table->text('postal_code')->nullable()->after('apartment_number');
            $table->text('city')->nullable()->after('postal_code');
            $table->text('pesel')->nullable()->after('city');
            $table->boolean('is_active')->default(true)->after('pesel');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'phone',
                'street',
                'building_number',
                'apartment_number',
                'postal_code',
                'city',
                'pesel',
                'is_active',
            ]);
        });
    }
};
