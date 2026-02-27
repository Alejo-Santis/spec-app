<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bundle_consumptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_bundle_id')->constrained()->restrictOnDelete();
            $table->integer('quantity');
            $table->timestamp('consumed_at');
            $table->string('description')->nullable();
            $table->string('reference')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bundle_consumptions');
    }
};
