<?php

declare(strict_types=1);

use App\Enums\Tenant\InventoryMovementTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('inventory_record_id')->constrained('inventory_records')->cascadeOnDelete();

            $table->enum('type', array_column(InventoryMovementTypeEnum::cases(), 'value'));

            $table->unsignedInteger('quantity');
            $table->unsignedInteger('quantity_before');
            $table->unsignedInteger('quantity_after');

            $table->string('note')->nullable();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
