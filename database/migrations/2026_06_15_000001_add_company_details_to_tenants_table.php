<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->string('tax_id')->nullable()->after('company_name');
            $table->string('regon')->nullable()->after('tax_id');
            $table->string('krs_number')->nullable()->after('regon');
            $table->string('company_email')->nullable()->after('krs_number');
            $table->string('company_phone')->nullable()->after('company_email');
            $table->string('website')->nullable()->after('company_phone');
            $table->string('street')->nullable()->after('website');
            $table->string('building_number')->nullable()->after('street');
            $table->string('apartment_number')->nullable()->after('building_number');
            $table->string('postal_code')->nullable()->after('apartment_number');
            $table->string('city')->nullable()->after('postal_code');
            $table->string('country')->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropColumn([
                'tax_id',
                'regon',
                'krs_number',
                'company_email',
                'company_phone',
                'website',
                'street',
                'building_number',
                'apartment_number',
                'postal_code',
                'city',
                'country',
            ]);
        });
    }
};
