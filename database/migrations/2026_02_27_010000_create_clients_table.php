<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['natural', 'juridica'])->default('juridica');
            $table->string('business_name');
            $table->string('trade_name')->nullable();
            $table->string('document_number')->unique();
            $table->string('dv', 1)->nullable();
            $table->enum('tax_regime', ['simple', 'ordinario'])->default('ordinario');
            $table->json('tax_responsibilities')->nullable();
            $table->string('ciiu_code')->nullable();
            $table->string('email')->nullable();
            $table->string('email_billing')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('department')->nullable();
            $table->string('country', 2)->default('CO');
            $table->string('postal_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
