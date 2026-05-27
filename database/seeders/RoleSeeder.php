<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // Ventas
            'crear ventas',
            'ver ventas sucursal',
            'consultar historial propio',
            'eliminar ventas',
            'cobrar tickets',
            
            // Tickets
            'reimprimir tickets autorizados',
            'reimprimir tickets sucursal',
            'consultar tickets',
            
            // Caja
            'caja apertura',
            'caja cierre',
            'caja cortes parciales',
            
            // Inventario & Productos
            'administrar inventario',
            'consultar productos',
            'alterar inventario manualmente',
            
            // Clientes
            'gestionar clientes',
            'consultar clientes',
            'editar perfil',
            
            // Descuentos
            'aplicar descuentos autorizados',
            'aplicar descuentos limitados',
            
            // Promociones
            'administrar promociones',
            'consultar promociones',
            
            // Devoluciones
            'aprobar devoluciones',
            'registrar devoluciones autorizadas',
            'solicitar devoluciones',
            
            // Creditos y Puntos
            'gestionar creditos',
            'ver saldo',
            'consultar puntos',
            
            // Facturacion
            'descargar facturas',
            'emitir facturas',
            'cancelar facturas',
            
            // Configuracion y Reportes
            'ver reportes tienda',
            'ver auditoria sucursal',
            'configurar metodos pago',
            'modificar impuestos'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // 1. SuperAdmin (Gate::before usually handles full access, but we create the role)
        $roleSuperAdmin = Role::firstOrCreate(['name' => 'superadmin']);
        $roleSuperAdmin->syncPermissions(Permission::all());

        // 2. Admin de Tienda
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleAdmin->syncPermissions([
            'administrar inventario',
            'ver ventas sucursal',
            'caja apertura',
            'caja cierre',
            'aplicar descuentos autorizados',
            'ver reportes tienda',
            'gestionar clientes',
            'aprobar devoluciones',
            'gestionar creditos',
            'ver auditoria sucursal',
            'configurar metodos pago',
            'administrar promociones',
            'reimprimir tickets sucursal',
            'emitir facturas',
            'cancelar facturas',
        ]);

        // 3. Vendedor / Cajero
        $roleVendedor = Role::firstOrCreate(['name' => 'vendedor']);
        $roleVendedor->syncPermissions([
            'crear ventas',
            'cobrar tickets',
            'reimprimir tickets autorizados',
            'consultar productos',
            'aplicar descuentos limitados',
            'consultar clientes',
            'registrar devoluciones autorizadas',
            'caja apertura',
            'caja cortes parciales',
            'consultar historial propio',
            'emitir facturas'
        ]);

        // 4. Cliente
        $roleCliente = Role::firstOrCreate(['name' => 'cliente']);
        $roleCliente->syncPermissions([
            'consultar historial propio',
            'ver saldo',
            'consultar puntos',
            'descargar facturas',
            'consultar tickets',
            'solicitar devoluciones',
            'consultar promociones',
            'editar perfil'
        ]);
    }
}
