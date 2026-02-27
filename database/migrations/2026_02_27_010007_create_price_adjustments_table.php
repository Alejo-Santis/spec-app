<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_price_id')->constrained()->cascadeOnDelete();
            $table->decimal('old_price', 15, 2);
            $table->decimal('new_price', 15, 2);
            $table->enum('reason', ['annual_adjust', 'negotiation', 'correction'])->default('annual_adjust');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_adjustments');
    }
};
