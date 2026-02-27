<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bundle_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('price_list_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->integer('quantity');
            $table->decimal('price', 15, 2);
            $table->decimal('unit_price', 15, 4)->storedAs('price / quantity');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bundle_tiers');
    }
};
