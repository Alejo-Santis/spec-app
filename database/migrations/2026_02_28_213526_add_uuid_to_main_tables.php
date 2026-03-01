<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

return new class extends Migration
{
    private array $tables = [
        'clients',
        'client_prices',
        'client_bundles',
        'price_lists',
        'bundle_tiers',
        'service_types',
        'users',
    ];

    public function up(): void
    {
        // 1. Añadir columna uuid nullable en las 7 tablas
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->uuid('uuid')->nullable()->unique()->after('id');
            });
        }

        // 2. Poblar uuid en registros existentes
        foreach ($this->tables as $table) {
            DB::table($table)->orderBy('id')->each(function ($row) use ($table) {
                DB::table($table)->where('id', $row->id)->update(['uuid' => (string) Uuid::uuid4()->toString()]);
            });
        }

        // 3. Hacer la columna NOT NULL
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->uuid('uuid')->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('uuid');
            });
        }
    }
};
