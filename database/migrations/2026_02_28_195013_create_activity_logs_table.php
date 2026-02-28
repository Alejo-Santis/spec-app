<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');                        // created, updated, deleted, activated, consumed
            $table->string('module');                        // Client, PriceList, ClientPrice, etc.
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_label')->nullable();     // Nombre/identificador del registro
            $table->text('description');                     // Descripción legible
            $table->json('properties')->nullable();          // Cambios old/new u otros datos
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['module', 'subject_id']);
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
