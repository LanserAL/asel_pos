<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\Branch;
use App\Models\Inventory;

class VendedorDashboard extends Component
{
    public $tenantName = '';
    public $lowStockCount = 0;
    public $totalSalesAllTime = 0;
    public $isSuspended = false;

    public function mount()
    {
        $user = auth()->user();
        if ($user && $user->tenant_id) {
            $tenant = Tenant::find($user->tenant_id);
            if ($user->hasRole('vendedor') && $user->branch_id) {
                $branch = Branch::find($user->branch_id);
                $this->tenantName = $tenant->name . ' - ' . ($branch ? $branch->name : 'Sucursal');
            } else {
                $this->tenantName = $tenant ? $tenant->name : 'Nuestra Tienda';
            }
            
            // Verificar si el tenant está suspendido
            $this->isSuspended = $tenant && $tenant->status !== 'active';
        } else {
            $tenant = Tenant::first();
            $this->tenantName = $tenant ? $tenant->name : 'Nuestra Tienda';
        }

        // Calculate low stock items across this tenant's branches
        if ($user && $user->tenant_id) {
            $query = Inventory::join('products', 'inventories.product_id', '=', 'products.id')
                ->where('products.tenant_id', $user->tenant_id)
                ->whereRaw('inventories.stock_quantity <= inventories.alert_min_stock');
                
            if ($user->hasRole('vendedor') && $user->branch_id) {
                $query->where('inventories.branch_id', $user->branch_id);
            }
            
            $this->lowStockCount = $query->count();
        } else {
            $this->lowStockCount = Inventory::whereRaw('stock_quantity <= alert_min_stock')->count();
        }
    }

    public $showCreateProductModal = false;
    public $newProductTitle = '';
    public $newProductDescription = '';
    public $newProductPrice = '';
    public $newProductSku = '';

    public function generateAIDescription()
    {
        $this->validate(['newProductTitle' => 'required|min:3']);
        
        $aiService = new \App\Services\AIService();
        $description = $aiService->generateProductDescription($this->newProductTitle);
        
        if ($description) {
            $this->newProductDescription = $description;
        } else {
            // Simulated fallback in case API fails
            $this->newProductDescription = "Excelente producto {$this->newProductTitle}, optimizado con características premium y la mejor calidad del mercado. ¡Ideal para ti!";
        }
    }

    public function createProduct()
    {
        $this->validate([
            'newProductTitle' => 'required',
            'newProductPrice' => 'required|numeric',
        ]);

        $user = auth()->user();
        Product::create([
            'tenant_id' => $user ? $user->tenant_id : 1,
            'title' => $this->newProductTitle,
            'slug' => \Illuminate\Support\Str::slug($this->newProductTitle) . '-' . time(),
            'sku' => $this->newProductSku ?? 'SKU-' . time(),
            'barcode' => 'BC-' . time(),
            'description' => $this->newProductDescription,
            'raw_title' => $this->newProductTitle,
            'price' => $this->newProductPrice,
            'cost' => $this->newProductPrice * 0.7, // Simulated cost
            'image_path' => 'https://images.unsplash.com/photo-1512418490979-9ce792d0397d?w=400&auto=format&fit=crop&q=60', // Mock image
            'status' => 'active',
        ]);

        $this->reset(['showCreateProductModal', 'newProductTitle', 'newProductDescription', 'newProductPrice', 'newProductSku']);
        session()->flash('message', 'Producto creado exitosamente.');
    }

    public function render()
    {
        $today = today();
        $user = auth()->user();
        $branchId = ($user && $user->hasRole('vendedor')) ? $user->branch_id : null;
        
        $ordersQuery = Order::whereDate('created_at', $today);
        if ($branchId) {
            $ordersQuery->where('branch_id', $branchId);
        }
        
        $totalSalesToday = (clone $ordersQuery)->sum('total');
        $ordersToday = (clone $ordersQuery)->count();
        
        $productsCount = Product::where('status', 'active')->count(); // Products are tenant-wide
        
        $recentOrdersQuery = Order::latest()->take(8);
        if ($branchId) {
            $recentOrdersQuery->where('branch_id', $branchId);
        }
        $recentOrders = $recentOrdersQuery->get();

        return view('livewire.vendedor-dashboard', [
            'totalSalesToday' => $totalSalesToday,
            'ordersToday' => $ordersToday,
            'productsCount' => $productsCount,
            'recentOrders' => $recentOrders,
            'lowStockCount' => $this->lowStockCount,
        ]);
    }
}
