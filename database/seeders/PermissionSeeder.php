<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Definir todos los permisos
        $permissions = [
            // Clientes
            'clients.view',
            'clients.create',
            'clients.update',
            'clients.delete',

            // Tipos de servicio
            'service-types.view',
            'service-types.manage',

            // Listas de precios
            'price-lists.view',
            'price-lists.create',
            'price-lists.activate',
            'price-lists.generate',

            // Bundle tiers
            'bundle-tiers.manage',

            // Precios por cliente
            'client-prices.view',
            'client-prices.create',
            'client-prices.update',
            'client-prices.delete',

            // Bolsas
            'client-bundles.view',
            'client-bundles.create',
            'client-bundles.update',
            'client-bundles.consume',

            // Log de actividades
            'activity-logs.view',

            // Usuarios
            'users.manage',

            // Importar / Exportar
            'import-export.use',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Asignar permisos por rol
        $admin    = Role::firstOrCreate(['name' => 'admin',    'guard_name' => 'web']);
        $operator = Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);
        $viewer   = Role::firstOrCreate(['name' => 'viewer',   'guard_name' => 'web']);

        // Admin: todos los permisos
        $admin->syncPermissions($permissions);

        // Operator: ver y operar, sin gestión de usuarios ni activar listas
        $operator->syncPermissions([
            'clients.view', 'clients.create', 'clients.update',
            'service-types.view',
            'price-lists.view',
            'client-prices.view', 'client-prices.create', 'client-prices.update',
            'client-bundles.view', 'client-bundles.create', 'client-bundles.update', 'client-bundles.consume',
            'activity-logs.view',
            'import-export.use',
        ]);

        // Viewer: solo lectura
        $viewer->syncPermissions([
            'clients.view',
            'service-types.view',
            'price-lists.view',
            'client-prices.view',
            'client-bundles.view',
            'activity-logs.view',
        ]);
    }
}
