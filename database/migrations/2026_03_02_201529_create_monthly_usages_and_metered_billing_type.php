<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ampliar el CHECK constraint de service_types.billing_type para incluir 'metered'
        DB::statement("ALTER TABLE service_types DROP CONSTRAINT IF EXISTS service_types_billing_type_check");
        DB::statement("ALTER TABLE service_types ADD CONSTRAINT service_types_billing_type_check CHECK (billing_type IN ('unit', 'bundle', 'metered'))");

        // 2. Crear tabla de consumos mensuales (facturación por uso real)
        Schema::create('monthly_usages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_price_id')->constrained()->restrictOnDelete();
            $table->smallInteger('period_year');           // Ej: 2026
            $table->smallInteger('period_month');          // 1–12
            $table->integer('document_count');             // Docs procesados en el período
            $table->decimal('unit_price', 15, 4);         // Snapshot del precio negociado
            $table->decimal('total_amount', 15, 2);       // document_count * unit_price
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            // Un solo registro por cliente/precio/período
            $table->unique(['client_price_id', 'period_year', 'period_month']);
            $table->index(['client_id', 'period_year', 'period_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_usages');
        DB::statement("ALTER TABLE service_types DROP CONSTRAINT IF EXISTS service_types_billing_type_check");
        DB::statement("ALTER TABLE service_types ADD CONSTRAINT service_types_billing_type_check CHECK (billing_type IN ('unit', 'bundle'))");
    }
};
