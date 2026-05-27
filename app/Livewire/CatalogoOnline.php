<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Tenant;
use App\Models\Inventory;
use App\Models\PaymentMethodSetting;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Str;

class CatalogoOnline extends Component
{
    public $search = '';
    public $tenant;
    public $branches = [];
    public $selectedBranchId = null;
    
    // Cart properties
    public $cart = [];
    public $showCart = false;
    
    // Checkout form
    public $customerName = '';
    public $customerPhone = '';
    public $paymentMethod = 'tarjeta';
    public $activePaymentMethods = [];
    
    // Shipping properties
    public $isShippingRequired = false;
    public $shippingAddress = '';

    public function mount()
    {
        $tenantSlug = request()->query('tenant');
        if ($tenantSlug) {
            $this->tenant = Tenant::where('slug', $tenantSlug)->where('status', 'active')->first();
        }
        
        if (!$this->tenant) {
            $this->tenant = Tenant::where('status', 'active')->first();
        }

        if ($this->tenant) {
            $this->branches = Branch::where('tenant_id', $this->tenant->id)->where('status', 'active')->get();
            $this->activePaymentMethods = PaymentMethodSetting::where('tenant_id', $this->tenant->id)
                ->where('is_enabled', true)
                ->get();
            
            if (count($this->activePaymentMethods) > 0) {
                $this->paymentMethod = $this->activePaymentMethods[0]->method_type;
            }

            // Prevent catalog leak by resetting branch and cart if store changes
            $sessionTenantId = session()->get('catalog_tenant_id');
            if ($sessionTenantId !== $this->tenant->id) {
                session()->put('catalog_tenant_id', $this->tenant->id);
                session()->forget('catalog_branch_id');
                session()->forget('catalog_cart');
            }
        }

        // Retrieve branch selection from session
        $this->selectedBranchId = session()->get('catalog_branch_id');
        
        // Restore cart from session
        $this->cart = session()->get('catalog_cart', []);
    }

    public function selectBranch($branchId)
    {
        $this->selectedBranchId = $branchId;
        session()->put('catalog_branch_id', $branchId);
        $this->clearCart();
    }

    public function changeBranch()
    {
        $this->selectedBranchId = null;
        session()->forget('catalog_branch_id');
        $this->clearCart();
    }

    public function render()
    {
        $products = collect();

        if ($this->selectedBranchId && $this->tenant) {
            $tenantId = $this->tenant->id;
            $branchId = $this->selectedBranchId;
            $searchQuery = $this->search;

            $products = Product::where('products.status', 'active')
                ->select('products.*', 'inventories.stock_quantity as stock')
                ->join('inventories', 'products.id', '=', 'inventories.product_id')
                ->where('inventories.branch_id', $this->selectedBranchId)
                ->where('inventories.stock_quantity', '>', 0) // Solo con stock
                ->when($this->search, function($query) {
                    $query->where(function($q) {
                        $q->where('products.title', 'like', '%' . $this->search . '%')
                          ->orWhere('products.description', 'like', '%' . $this->search . '%');
                    });
                })
                ->get();
        }

        return view('livewire.catalogo-online', [
            'products' => $products,
            'cartCount' => collect($this->cart)->sum('quantity'),
            'cartSubtotal' => collect($this->cart)->sum('total'),
        ]);
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);
        if (!$product) return;

        // Check stock
        $inventory = Inventory::where('branch_id', $this->selectedBranchId)
            ->where('product_id', $product->id)
            ->first();

        $stock = $inventory ? $inventory->stock_quantity : 0;

        $qtyInCart = isset($this->cart[$productId]) ? $this->cart[$productId]['quantity'] : 0;

        if ($qtyInCart >= $stock) {
            session()->flash('error', "No hay suficiente stock de {$product->title} (Disponible: {$stock}).");
            return;
        }

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']++;
            $this->cart[$productId]['total'] = $this->cart[$productId]['quantity'] * $this->cart[$productId]['price'];
        } else {
            $this->cart[$productId] = [
                'id' => $product->id,
                'name' => $product->title,
                'price' => $product->price,
                'quantity' => 1,
                'total' => $product->price,
                'image' => $product->image_path,
            ];
        }

        session()->put('catalog_cart', $this->cart);
        session()->flash('success', "{$product->title} agregado al carrito.");
    }

    public function updateQuantity($productId, $quantity)
    {
        $quantity = intval($quantity);
        if ($quantity <= 0) {
            $this->removeFromCart($productId);
            return;
        }

        // Check stock
        $inventory = Inventory::where('branch_id', $this->selectedBranchId)
            ->where('product_id', $productId)
            ->first();

        $stock = $inventory ? $inventory->stock_quantity : 0;

        if ($quantity > $stock) {
            session()->flash('error', "No hay suficiente stock disponible (Máximo: {$stock}).");
            $this->cart[$productId]['quantity'] = $stock;
            $this->cart[$productId]['total'] = $stock * $this->cart[$productId]['price'];
        } else {
            $this->cart[$productId]['quantity'] = $quantity;
            $this->cart[$productId]['total'] = $quantity * $this->cart[$productId]['price'];
        }

        session()->put('catalog_cart', $this->cart);
    }

    public function removeFromCart($productId)
    {
        if (isset($this->cart[$productId])) {
            unset($this->cart[$productId]);
            session()->put('catalog_cart', $this->cart);
        }
    }

    public function clearCart()
    {
        $this->cart = [];
        session()->forget('catalog_cart');
    }

    public function checkout()
    {
        $rules = [
            'customerName' => 'required|string|max:100',
            'customerPhone' => 'required|string|min:10|max:15',
            'paymentMethod' => 'required|string',
        ];

        if ($this->isShippingRequired) {
            $rules['shippingAddress'] = 'required|string|min:10|max:500';
        }

        $this->validate($rules, [
            'customerName.required' => 'Tu nombre es obligatorio.',
            'customerPhone.required' => 'El teléfono de contacto es obligatorio.',
            'customerPhone.min' => 'El teléfono debe tener al menos 10 dígitos.',
            'paymentMethod.required' => 'Debes seleccionar un método de pago.',
            'shippingAddress.required' => 'La dirección de entrega es obligatoria para envíos a domicilio.',
            'shippingAddress.min' => 'Por favor escribe una dirección más detallada.',
        ]);

        if (empty($this->cart)) {
            session()->flash('error', 'El carrito está vacío.');
            return;
        }

        // Calculate Totals
        $subtotal = collect($this->cart)->sum('total');
        $tax = $subtotal * 0.16;
        $total = $subtotal + $tax;

        try {
            $order = \Illuminate\Support\Facades\DB::transaction(function () use ($subtotal, $tax, $total) {
                // Validate stock again and deduct under lockForUpdate
                foreach ($this->cart as $item) {
                    $inventory = Inventory::where('branch_id', $this->selectedBranchId)
                        ->where('product_id', $item['id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$inventory || $inventory->stock_quantity < $item['quantity']) {
                        throw new \Exception("El producto {$item['name']} ya no tiene suficiente stock disponible (Disponible: " . ($inventory ? $inventory->stock_quantity : 0) . ").");
                    }

                    // Deduct stock immediately
                    $inventory->stock_quantity -= $item['quantity'];
                    $inventory->save();
                }

                // Create the order in DB
                $order = Order::create([
                    'tenant_id' => $this->tenant->id,
                    'branch_id' => $this->selectedBranchId,
                    'user_id' => null, // Guest order
                    'customer_name_manual' => $this->customerName,
                    'customer_phone' => $this->customerPhone,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $total,
                    'payment_method' => $this->paymentMethod,
                    'payment_status' => 'pending',
                    'delivery_status' => 'pendiente',
                    'source' => 'catalog_online',
                    'is_shipping_required' => $this->isShippingRequired,
                    'shipping_address' => $this->isShippingRequired ? $this->shippingAddress : null,
                    'shipping_cost' => 0.00, // Cotización interna
                ]);

                // Create Order Items
                foreach ($this->cart as $item) {
                    \App\Models\OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['id'],
                        'product_name_backup' => $item['name'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'total' => $item['total'],
                    ]);
                }

                return $order;
            });

            // Clear cart and checkout fields
            $this->clearCart();
            $this->showCart = false;
            $this->customerName = '';
            $this->customerPhone = '';
            $this->isShippingRequired = false;
            $this->shippingAddress = '';

            return redirect()->route('orders.tracking', ['orderId' => $order->id, 'success' => 'true']);

        } catch (\Exception $e) {
            session()->flash('error', "Error al procesar el pedido: " . $e->getMessage());
            return;
        }
    }
}
