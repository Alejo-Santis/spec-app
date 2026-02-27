<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('price_list_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('duration_years')->nullable();
            $table->decimal('base_price', 15, 2);
            $table->decimal('adjustment_percentage', 5, 2)->nullable();
            $table->decimal('negotiated_price', 15, 2)->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->decimal('final_price', 15, 2);
            $table->boolean('applies_iva')->default(false);
            $table->decimal('iva_percentage', 5, 2)->default(19.00);
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['client_id', 'service_type_id', 'price_list_id', 'duration_years']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_prices');
    }
};
