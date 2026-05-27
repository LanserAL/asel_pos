<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\PaymentMethodSetting;
use App\Models\Order;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Spatie Roles using RoleSeeder
        $this->call(RoleSeeder::class);

        $superAdminRole = Role::where('name', 'superadmin')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $vendedorRole = Role::where('name', 'vendedor')->first();

        // 2. Create Global Super Admin User
        $superAdmin = User::create([
            'tenant_id' => null,
            'name' => 'Super Administrador OmniPOS',
            'email' => 'superadmin@omnipos.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        $superAdmin->assignRole($superAdminRole);

        // 3. Create Tenant
        $tenant = Tenant::create([
            'name' => 'Tienda de Conveniencia Asel',
            'slug' => 'tienda-asel',
            'logo_path' => 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?w=200&auto=format&fit=crop&q=60&ixlib=rb-4.0.3',
            'description' => 'Tu tienda de confianza de abarrotes, tecnología y artículos de conveniencia de alta calidad.',
            'status' => 'active',
            'plan_capacity' => [
                'max_branches' => 5,
                'max_products' => 100,
                'max_users' => 10,
            ],
            'expires_at' => now()->addYear(),
        ]);

        // 4. Create Branches
        $branch1 = Branch::create([
            'tenant_id' => $tenant->id,
            'name' => 'Sucursal Matriz Centro',
            'address' => 'Av. Juárez 150, Centro Histórico, CDMX, C.P. 06000',
            'phone' => '5512345678',
            'status' => 'active',
        ]);

        $branch2 = Branch::create([
            'tenant_id' => $tenant->id,
            'name' => 'Sucursal Plaza Poniente',
            'address' => 'Calzada de las Águilas 450, Local 3, Álvaro Obregón, CDMX',
            'phone' => '5587654321',
            'status' => 'active',
        ]);

        // 5. Create Tenant Users (scoped to tenant)
        $tenantAdmin = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Luis Fernando (Admin Asel)',
            'email' => 'admin@tienda.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        $tenantAdmin->assignRole($adminRole);

        $vendedor = User::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch1->id,
            'name' => 'Carlos López (Vendedor)',
            'email' => 'vendedor@tienda.com',
            'password' => Hash::make('password'),
            'role' => 'vendedor',
        ]);
        $vendedor->assignRole($vendedorRole);

        // 6. Create Payment Method Settings
        PaymentMethodSetting::create([
            'tenant_id' => $tenant->id,
            'method_type' => 'efectivo',
            'is_enabled' => true,
            'instructions' => 'Pago directo en caja registradora al recibir los productos.',
        ]);

        PaymentMethodSetting::create([
            'tenant_id' => $tenant->id,
            'method_type' => 'transferencia',
            'is_enabled' => true,
            'instructions' => "Banco: BBVA Bancomer\nCLABE: 012180009876543219\nBeneficiario: ASEL POS S.A. de C.V.\nReferencia: Ingresar número de ticket.",
        ]);

        PaymentMethodSetting::create([
            'tenant_id' => $tenant->id,
            'method_type' => 'tarjeta',
            'is_enabled' => true,
            'instructions' => 'Terminal Point de Mercado Pago física. Aceptamos todas las tarjetas de débito y crédito.',
        ]);

        // 7. Create Products
        $productsData = [
            [
                'title' => 'Coca-Cola Original 600ml',
                'sku' => 'CC-600ML',
                'barcode' => '7501055300010',
                'description' => 'Refresco de cola clásico y burbujeante, perfecto para acompañar cualquier comida. Servir bien frío.',
                'price' => 18.50,
                'cost' => 12.00,
                'image_path' => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=400&auto=format&fit=crop&q=60',
            ],
            [
                'title' => 'Papas Fritas Sabritas Original 110g',
                'sku' => 'SAB-110G',
                'barcode' => '7501011110057',
                'description' => 'Papas fritas clásicas Sabritas con la sal exacta. Crujientes, deliciosas y perfectas para compartir.',
                'price' => 24.00,
                'cost' => 16.00,
                'image_path' => 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=400&auto=format&fit=crop&q=60',
            ],
            [
                'title' => 'Agua Mineral Topo Chico 355ml',
                'sku' => 'TC-355ML',
                'barcode' => '7501055303660',
                'description' => 'Agua mineral carbonatada originaria de Monterrey. Extra refrescante y con un burbujeo legendario.',
                'price' => 26.00,
                'cost' => 17.50,
                'image_path' => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=400&auto=format&fit=crop&q=60', // Mock fallback
            ],
            [
                'title' => 'Chocolate Snickers Barra 48g',
                'sku' => 'SN-48G',
                'barcode' => '040000004319',
                'description' => 'Barra de chocolate con leche rellena de turrón, caramelo cremoso y cacahuates crujientes. ¡No eres tú cuando tienes hambre!',
                'price' => 22.00,
                'cost' => 14.00,
                'image_path' => 'https://images.unsplash.com/photo-1581798459219-318e76aecc7b?w=400&auto=format&fit=crop&q=60',
            ],
            [
                'title' => 'Cargador Rápido USB-C 20W',
                'sku' => 'CHG-20W',
                'barcode' => '194252156972',
                'description' => 'Adaptador de corriente USB-C de 20W para carga ultra rápida de dispositivos móviles y tablets.',
                'price' => 349.00,
                'cost' => 150.00,
                'image_path' => 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=400&auto=format&fit=crop&q=60',
            ],
            [
                'title' => 'Audífonos Bluetooth Pro',
                'sku' => 'AUD-BT-PRO',
                'barcode' => '697259610222',
                'description' => 'Audífonos inalámbricos in-ear con cancelación de ruido activa, estuche de carga inteligente y hasta 24 horas de batería.',
                'price' => 599.00,
                'cost' => 260.00,
                'image_path' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&auto=format&fit=crop&q=60',
            ],
            [
                'title' => 'Café Americano Gourmet 12oz',
                'sku' => 'CAF-AME-12',
                'barcode' => '880101901111',
                'description' => 'Café caliente de grano seleccionado 100% Arábica con notas achocolatadas. Preparado al instante.',
                'price' => 32.00,
                'cost' => 8.50,
                'image_path' => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=400&auto=format&fit=crop&q=60',
            ],
            [
                'title' => 'Galletas Chokis Clásicas 57g',
                'sku' => 'CHO-57G',
                'barcode' => '7501011130987',
                'description' => 'Galletas crujientes con deliciosas chispas de chocolate sabor tradicional de Gamesa.',
                'price' => 17.00,
                'cost' => 11.20,
                'image_path' => 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=400&auto=format&fit=crop&q=60',
            ],
            [
                'title' => 'Mouse Óptico Inalámbrico USB',
                'sku' => 'MSE-IN-USB',
                'barcode' => '097855146522',
                'description' => 'Mouse inalámbrico ergonómico con receptor nano USB, sensor óptico de alta precisión 1600 DPI y batería de larga duración.',
                'price' => 189.00,
                'cost' => 75.00,
                'image_path' => 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?w=400&auto=format&fit=crop&q=60',
            ],
            [
                'title' => 'Gansito Marinela 50g',
                'sku' => 'GAN-50G',
                'barcode' => '7501000112304',
                'description' => 'Pastelito relleno de crema y mermelada de fresa, con cobertura de chocolate y granillo. Un ícono dulce de México.',
                'price' => 16.50,
                'cost' => 10.50,
                'image_path' => 'https://images.unsplash.com/photo-1551024601-bec78aea704b?w=400&auto=format&fit=crop&q=60', // Mock fallback
            ],
        ];

        $products = [];
        foreach ($productsData as $data) {
            $products[] = Product::create([
                'tenant_id' => $tenant->id,
                'title' => $data['title'],
                'slug' => Str::slug($data['title']),
                'sku' => $data['sku'],
                'barcode' => $data['barcode'],
                'description' => $data['description'],
                'raw_title' => $data['title'],
                'price' => $data['price'],
                'cost' => $data['cost'],
                'image_path' => $data['image_path'],
                'status' => 'active',
            ]);
        }

        // 8. Create Inventories for each branch
        // Branch 1 - Matriz Centro stocks (Some items intentionally low)
        $stocksBranch1 = [
            'CC-600ML' => ['qty' => 65, 'min' => 10],
            'SAB-110G' => ['qty' => 45, 'min' => 8],
            'TC-355ML' => ['qty' => 3, 'min' => 5],   // Trigger Low Stock Aler!
            'SN-48G' => ['qty' => 18, 'min' => 5],
            'CHG-20W' => ['qty' => 12, 'min' => 3],
            'AUD-BT-PRO' => ['qty' => 2, 'min' => 3], // Trigger Low Stock Alert!
            'CAF-AME-12' => ['qty' => 120, 'min' => 15],
            'CHO-57G' => ['qty' => 30, 'min' => 8],
            'MSE-IN-USB' => ['qty' => 8, 'min' => 2],
            'GAN-50G' => ['qty' => 4, 'min' => 5],   // Trigger Low Stock Alert!
        ];

        // Branch 2 - Plaza Poniente stocks
        $stocksBranch2 = [
            'CC-600ML' => ['qty' => 40, 'min' => 10],
            'SAB-110G' => ['qty' => 25, 'min' => 8],
            'TC-355ML' => ['qty' => 15, 'min' => 5],
            'SN-48G' => ['qty' => 10, 'min' => 5],
            'CHG-20W' => ['qty' => 5, 'min' => 3],
            'AUD-BT-PRO' => ['qty' => 4, 'min' => 2],
            'CAF-AME-12' => ['qty' => 0, 'min' => 15], // Out of stock completely!
            'CHO-57G' => ['qty' => 15, 'min' => 5],
            'MSE-IN-USB' => ['qty' => 3, 'min' => 2],
            'GAN-50G' => ['qty' => 15, 'min' => 5],
        ];

        foreach ($products as $product) {
            // Seed Branch 1
            if (isset($stocksBranch1[$product->sku])) {
                Inventory::create([
                    'branch_id' => $branch1->id,
                    'product_id' => $product->id,
                    'stock_quantity' => $stocksBranch1[$product->sku]['qty'],
                    'alert_min_stock' => $stocksBranch1[$product->sku]['min'],
                ]);
            }

            // Seed Branch 2
            if (isset($stocksBranch2[$product->sku])) {
                Inventory::create([
                    'branch_id' => $branch2->id,
                    'product_id' => $product->id,
                    'stock_quantity' => $stocksBranch2[$product->sku]['qty'],
                    'alert_min_stock' => $stocksBranch2[$product->sku]['min'],
                ]);
            }
        }

        // 9. Create Historical Orders for dashboards
        $methods = ['efectivo', 'transferencia', 'tarjeta'];
        $statuses = ['paid', 'pending'];
        $deliveries = ['pendiente', 'preparando', 'enviado', 'entregado'];
        $sources = ['pos', 'catalog_online'];
        $customers = [
            ['name' => 'Juan Pérez', 'phone' => '5511223344'],
            ['name' => 'María García', 'phone' => '5599887766'],
            ['name' => 'Alejandro Ruiz', 'phone' => '5544332211'],
            ['name' => 'Sofía Torres', 'phone' => '5566778899'],
            ['name' => 'Roberto Sánchez', 'phone' => '5577665544'],
        ];

        // Seed some historical sales over the last 10 days
        for ($i = 10; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $numOrders = $i === 0 ? rand(2, 4) : rand(1, 3); // More sales today!

            for ($j = 0; $j < $numOrders; $j++) {
                $subtotal = rand(50, 450);
                $tax = $subtotal * 0.16;
                $total = $subtotal + $tax;
                $customer = $customers[array_rand($customers)];
                
                $source = $sources[array_rand($sources)];
                $paymentStatus = 'paid';
                $deliveryStatus = 'entregado';

                if ($source === 'catalog_online') {
                    $paymentStatus = $statuses[array_rand($statuses)];
                    $deliveryStatus = $deliveries[array_rand($deliveries)];
                }

                Order::create([
                    'tenant_id' => $tenant->id,
                    'branch_id' => rand(0, 1) === 0 ? $branch1->id : $branch2->id,
                    'user_id' => $vendedor->id,
                    'customer_name_manual' => $customer['name'],
                    'customer_phone' => $customer['phone'],
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $total,
                    'payment_method' => $methods[array_rand($methods)],
                    'payment_status' => $paymentStatus,
                    'delivery_status' => $deliveryStatus,
                    'source' => $source,
                    'created_at' => $date->copy()->subHours(rand(1, 10)),
                ]);
            }
        }
    }
}
