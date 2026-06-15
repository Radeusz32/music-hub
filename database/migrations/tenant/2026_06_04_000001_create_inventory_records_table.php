<?php

declare(strict_types=1);

use App\Enums\Tenant\DiscConditionEnum;
use App\Enums\Tenant\DiscFormatEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_records', function (Blueprint $table): void {
            $table->id();

            $table->string('name');
            $table->string('artist');
            $table->string('genre');

            $table->enum('format', array_column(DiscFormatEnum::cases(), 'value'));
            $table->enum('condition', array_column(DiscConditionEnum::cases(), 'value'))
                ->default(DiscConditionEnum::NearMint->value);

            $table->string('label')->nullable();
            $table->string('catalog_number')->nullable();
            $table->string('barcode')->nullable();
            $table->string('country')->nullable();
            $table->unsignedSmallInteger('year')->nullable();

            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('purchase_price_per_unit', 10, 2)->nullable();

            $table->string('cover_image')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_records');
    }
};
