<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Order;
use App\Models\Branch;
use App\Models\Tenant;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TerminalPos extends Component
{
    public $search = '';
    public $category = 'Todos';
    public $cart = [];
    public $total = 0;
    public $subtotal = 0;
    public $tax = 0;
    public $paymentMethod = 'Efectivo';
    public $showPaymentModal = false;

    public $branches = [];
    public $branchId;
    public $tenantId;
    public $activeCurrency = 'MXN';
    public $pairingToken;

    // Cash Register Session state
    public $cashRegisterSessionId;
    public $isBoxOpen = false;
    public $openingAmount = 0;
    public $openingNotes = '';
    public $openingPassword = '';  // Password to verify before opening box
    
    public $closingAmount = 0;
    public $closingNotes = '';
    public $closingPassword = '';  // Password to verify before closing box
    public $cashSalesTotal = 0;
    public $expectedCash = 0;
    public $manualInflows = 0;
    public $manualOutflows = 0;

    public $transactionType = 'in';
    public $transactionAmount = 0;
    public $transactionReason = '';

    // Discount state
    public $discountType = 'percentage'; // percentage, fixed
    public $discountValue = 0;
    public $discountReason = '';
    public $appliedDiscount = 0;
    public $couponCode = '';             // Store typed promo code
    public $appliedPromotions = [];      // Store auto-applied promotions

    // Customer / Credit / Loyalty state
    public $selectedCustomerId = null;
    public $selectedCustomer = null;
    public $customerQuery = '';
    public $customersList = [];
    public $isPayingWithCredit = false;
    public $isPayingWithPoints = false;
    public $pointsToRedeem = 0;
    public $pointsDiscountAmount = 0;
    public $shouldInvoiceImmediate = false;

    public $showOpenBoxModal = false;
    public $showCloseBoxModal = false;
    public $showTransactionModal = false;
    public $showDiscountModal = false;

    public function mount()
    {
        $user = auth()->user();
        if ($user && $user->tenant_id) {
            $this->tenantId = $user->tenant_id;
            if ($user->hasRole('vendedor') && $user->branch_id) {
                // Vendedor can only see their own branch
                $this->branches = Branch::where('id', $user->branch_id)->get();
                $this->branchId = $user->branch_id;
                session()->put('selected_branch_id', $this->branchId);
            } else {
                // Admin can see all branches
                $this->branches = Branch::where('tenant_id', $this->tenantId)->get();
                $this->branchId = session()->get('selected_branch_id');
                if (!$this->branchId && count($this->branches) > 0) {
                    $this->branchId = $this->branches[0]->id;
                    session()->put('selected_branch_id', $this->branchId);
                }
            }
        } else {
            // Testing fallback
            $tenant = Tenant::first();
            $this->tenantId = $tenant ? $tenant->id : null;
            $this->branches = $this->tenantId ? Branch::where('tenant_id', $this->tenantId)->get() : [];
            $this->branchId = count($this->branches) > 0 ? $this->branches[0]->id : null;
        }

        // Generar un token único de emparejamiento para el escáner móvil
        $this->pairingToken = session()->get('pos_pairing_token', 'POS_' . \Illuminate\Support\Str::random(12));
        session()->put('pos_pairing_token', $this->pairingToken);

        $this->checkCashRegisterSession();
        $this->loadActiveCurrency();
    }

    public function loadActiveCurrency()
    {
        if ($this->branchId) {
            $branch = Branch::find($this->branchId);
            $this->activeCurrency = $branch ? ($branch->currency ?? 'MXN') : 'MXN';
        } else {
            $this->activeCurrency = 'MXN';
        }
    }

    public function formatPrice($amount, $currencyCode = null)
    {
        $currency = $currencyCode ?: $this->activeCurrency;
        
        switch ($currency) {
            case 'USD':
                return 'US$' . number_format($amount, 2);
            case 'EUR':
                return number_format($amount, 2) . ' €';
            case 'COP':
                return 'COL$' . number_format($amount, 2);
            case 'MXN':
            default:
                return '$' . number_format($amount, 2);
        }
    }

    public function checkCashRegisterSession()
    {
        if (!$this->branchId || !$this->tenantId) {
            $this->isBoxOpen = false;
            $this->cashRegisterSessionId = null;
            return;
        }

        // Proactively fetch or create a default Cash Register for this branch
        $register = \App\Models\CashRegister::firstOrCreate(
            ['branch_id' => $this->branchId, 'tenant_id' => $this->tenantId],
            ['name' => 'Caja Principal', 'status' => 'active']
        );

        // Find active open session
        $session = \App\Models\CashRegisterSession::where('cash_register_id', $register->id)
            ->where('opened_by', auth()->id())
            ->where('status', 'open')
            ->first();

        if ($session) {
            $this->cashRegisterSessionId = $session->id;
            $this->isBoxOpen = true;
        } else {
            $this->cashRegisterSessionId = null;
            $this->isBoxOpen = false;
        }
    }

    public function changeBranch($branchId)
    {
        if (auth()->user() && auth()->user()->hasRole('vendedor')) {
            return; // Prevent Vendedores from changing branch
        }
        $this->branchId = $branchId;
        session()->put('selected_branch_id', $branchId);
        $this->clearCart();
        $this->loadActiveCurrency();
        $this->checkCashRegisterSession();
    }

    public function updatedCustomerQuery()
    {
        if (strlen($this->customerQuery) >= 2) {
            $this->customersList = \App\Models\Customer::where('name', 'like', '%' . $this->customerQuery . '%')
                ->orWhere('phone', 'like', '%' . $this->customerQuery . '%')
                ->orWhere('rfc', 'like', '%' . $this->customerQuery . '%')
                ->take(5)
                ->get();
        } else {
            $this->customersList = [];
        }
    }

    public function selectCustomer($id)
    {
        $this->selectedCustomerId = $id;
        $this->selectedCustomer = \App\Models\Customer::find($id);
        $this->customerQuery = '';
        $this->customersList = [];
        
        // Reset points & credit states
        $this->isPayingWithCredit = false;
        $this->isPayingWithPoints = false;
        $this->pointsToRedeem = 0;
        $this->pointsDiscountAmount = 0;
        
        $this->calculateTotals();
    }

    public function clearSelectedCustomer()
    {
        $this->reset(['selectedCustomerId', 'selectedCustomer', 'customerQuery', 'customersList', 'isPayingWithCredit', 'isPayingWithPoints', 'pointsToRedeem', 'pointsDiscountAmount']);
        $this->calculateTotals();
    }

    public function togglePayWithPoints()
    {
        $this->calculateTotals();
    }

    public function render()
    {
        $products = collect();

        if ($this->branchId) {
            $products = Product::where('products.status', 'active')
                ->select('products.*', 'inventories.stock_quantity as stock', 'inventories.alert_min_stock')
                ->join('inventories', 'products.id', '=', 'inventories.product_id')
                ->where('inventories.branch_id', $this->branchId)
                ->when($this->search, function($query) {
                    $query->where(function($q) {
                        $q->where('products.title', 'like', '%' . $this->search . '%')
                          ->orWhere('products.sku', 'like', '%' . $this->search . '%')
                          ->orWhere('products.barcode', 'like', '%' . $this->search . '%');
                    });
                })
                ->get();
        }

        return view('livewire.terminal-pos', [
            'products' => $products
        ]);
    }

    public function setCategory($category)
    {
        $this->category = $category;
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);
        if (!$product) return;

        // Get inventory for stock checking
        $inventory = Inventory::where('branch_id', $this->branchId)
            ->where('product_id', $product->id)
            ->first();

        $stock = $inventory ? $inventory->stock_quantity : 0;

        // Check if there is enough stock
        $qtyInCart = 0;
        foreach ($this->cart as $item) {
            if ($item['id'] == $product->id) {
                $qtyInCart = $item['quantity'];
                break;
            }
        }

        if ($qtyInCart >= $stock) {
            session()->flash('error', "No hay suficiente stock de {$product->title} (Stock disponible: {$stock}).");
            return;
        }

        $found = false;
        foreach ($this->cart as &$item) {
            if ($item['id'] == $product->id) {
                $item['quantity']++;
                $item['total'] = $item['quantity'] * $item['price'];
                $found = true;
                break;
            }
        }

        if (!$found) {
            $this->cart[] = [
                'id' => $product->id,
                'name' => $product->title,
                'price' => $product->price,
                'quantity' => 1,
                'total' => $product->price
            ];
        }

        $this->calculateTotals();
    }

    public function removeFromCart($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->calculateTotals();
    }

    public function increaseQuantity($index)
    {
        $item = $this->cart[$index];
        $inventory = Inventory::where('branch_id', $this->branchId)
            ->where('product_id', $item['id'])
            ->first();

        $stock = $inventory ? $inventory->stock_quantity : 0;

        if ($item['quantity'] >= $stock) {
            session()->flash('error', "No hay suficiente stock de {$item['name']} (Stock disponible: {$stock}).");
            return;
        }

        $this->cart[$index]['quantity']++;
        $this->cart[$index]['total'] = $this->cart[$index]['quantity'] * $this->cart[$index]['price'];
        $this->calculateTotals();
    }

    public function decreaseQuantity($index)
    {
        if ($this->cart[$index]['quantity'] > 1) {
            $this->cart[$index]['quantity']--;
            $this->cart[$index]['total'] = $this->cart[$index]['quantity'] * $this->cart[$index]['price'];
            $this->calculateTotals();
        } else {
            $this->removeFromCart($index);
        }
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->subtotal = collect($this->cart)->sum('total');

        if (empty($this->cart)) {
            $this->appliedDiscount = 0;
            $this->appliedPromotions = [];
            $this->tax = 0;
            $this->total = 0;
            return;
        }

        // Invoke the advanced automated promotions calculator
        $promoAction = new \App\Actions\ApplyPromotionsToCartAction();
        $result = $promoAction->execute($this->cart, (int)$this->tenantId, $this->couponCode ?: null);

        $this->appliedPromotions = $result['applied_promotions'];
        $this->appliedDiscount = $result['total_discount'];
        
        if ($this->isPayingWithPoints && $this->selectedCustomer) {
            $maxPointsNeeded = (int)ceil($result['total']);
            $pointsToUse = min($this->selectedCustomer->loyalty_points, $maxPointsNeeded);
            $this->pointsToRedeem = $pointsToUse;
            $this->pointsDiscountAmount = $pointsToUse * 1.00;
            
            $this->total = max(0, $result['total'] - $this->pointsDiscountAmount);
        } else {
            $this->pointsToRedeem = 0;
            $this->pointsDiscountAmount = 0;
            $this->total = $result['total'];
        }

        $this->tax = $result['tax'];
    }

    public function applyDiscount()
    {
        $this->validate([
            'discountValue' => 'required|numeric|min:0',
            'discountReason' => 'required|string|min:5',
        ], [
            'discountReason.required' => 'El motivo del descuento es obligatorio.',
            'discountReason.min' => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        $user = auth()->user();

        // Enforce maximum discount limit by role
        if ($user->hasRole('vendedor')) {
            session()->flash('error', 'Los vendedores no tienen autorización para aplicar descuentos.');
            return;
        }

        $this->calculateTotals();
        $this->showDiscountModal = false;
        session()->flash('success', '¡Descuento aplicado correctamente!');
    }

    public function removeDiscount()
    {
        $this->reset(['discountValue', 'discountReason', 'appliedDiscount']);
        $this->calculateTotals();
        session()->flash('success', 'Descuento eliminado de la venta.');
    }

    public function processPayment()
    {
        if (empty($this->cart)) return;

        if (!$this->isBoxOpen) {
            session()->flash('error', "No puedes realizar ventas sin tener una sesión de caja abierta.");
            return;
        }

        if (strtolower($this->paymentMethod) === 'credito') {
            if (!$this->selectedCustomerId || !$this->selectedCustomer) {
                session()->flash('error', "Para vender a crédito es obligatorio seleccionar un cliente registrado.");
                return;
            }
            $availableCredit = $this->selectedCustomer->credit_limit - $this->selectedCustomer->credit_balance;
            if ($this->total > $availableCredit) {
                session()->flash('error', "Límite de crédito excedido. Disponible: $" . number_format($availableCredit, 2));
                return;
            }
        }

        try {
            DB::transaction(function () {
                // Loop through cart and deduct stock with lockForUpdate
                foreach ($this->cart as $item) {
                    $inventory = Inventory::where('branch_id', $this->branchId)
                        ->where('product_id', $item['id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$inventory || $inventory->stock_quantity < $item['quantity']) {
                        throw new \Exception("Stock insuficiente para el producto: {$item['name']}");
                    }

                    $inventory->stock_quantity -= $item['quantity'];
                    $inventory->save();
                }

                $reason = $this->discountReason;
                if ($this->appliedDiscount > 0 && !empty($this->appliedPromotions)) {
                    $reason = implode(', ', collect($this->appliedPromotions)->pluck('name')->toArray());
                }

                // Create Order
                $order = Order::create([
                    'tenant_id' => $this->tenantId,
                    'branch_id' => $this->branchId,
                    'user_id' => auth()->id() ?? User::where('tenant_id', $this->tenantId)->where('role', 'vendedor')->first()->id ?? null,
                    'customer_name_manual' => $this->selectedCustomer ? $this->selectedCustomer->name : 'Cliente de Caja POS',
                    'customer_phone' => $this->selectedCustomer ? $this->selectedCustomer->phone : null,
                    'subtotal' => $this->subtotal,
                    'tax' => $this->tax,
                    'total' => $this->total,
                    'payment_method' => strtolower($this->paymentMethod),
                    'payment_status' => 'paid',
                    'delivery_status' => 'entregado',
                    'source' => 'pos',
                    'cash_register_session_id' => $this->cashRegisterSessionId,
                    'discount_amount' => $this->appliedDiscount,
                    'discount_reason' => $this->appliedDiscount > 0 ? $reason : null,
                    'discount_authorized_by' => $this->appliedDiscount > 0 ? auth()->id() : null,
                    'currency' => $this->activeCurrency,
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

                // CREDIT CHARGE TRANSACTION
                if (strtolower($this->paymentMethod) === 'credito') {
                    \App\Models\CustomerCreditTransaction::create([
                        'tenant_id' => $this->tenantId,
                        'customer_id' => $this->selectedCustomer->id,
                        'order_id' => $order->id,
                        'type' => 'charge',
                        'amount' => $this->total,
                        'notes' => 'Consumo a cuenta de crédito de tienda',
                        'processed_by' => auth()->id(),
                    ]);
                    
                    $this->selectedCustomer->credit_balance += $this->total;
                    $this->selectedCustomer->save();
                }

                // LOYALTY EARN / REDEEM
                if ($this->selectedCustomer) {
                    $pointsEarned = (int)floor($this->total / 10);
                    if ($pointsEarned > 0) {
                        \App\Models\LoyaltyTransaction::create([
                            'tenant_id' => $this->tenantId,
                            'customer_id' => $this->selectedCustomer->id,
                            'order_id' => $order->id,
                            'type' => 'earn',
                            'points' => $pointsEarned,
                            'value_amount' => $pointsEarned * 1.00,
                        ]);
                        
                        $this->selectedCustomer->loyalty_points += $pointsEarned;
                        $this->selectedCustomer->save();
                    }

                    if ($this->isPayingWithPoints && $this->pointsToRedeem > 0) {
                        \App\Models\LoyaltyTransaction::create([
                            'tenant_id' => $this->tenantId,
                            'customer_id' => $this->selectedCustomer->id,
                            'order_id' => $order->id,
                            'type' => 'redeem',
                            'points' => $this->pointsToRedeem,
                            'value_amount' => $this->pointsDiscountAmount,
                        ]);
                        
                        $this->selectedCustomer->loyalty_points -= $this->pointsToRedeem;
                        $this->selectedCustomer->save();
                    }
                }

                // IMMEDIATE CFDI INVOICING
                if ($this->shouldInvoiceImmediate && $this->selectedCustomer) {
                    $lastInvoice = \App\Models\Invoice::latest()->first();
                    $nextFolio = $lastInvoice ? ($lastInvoice->folio + 1) : 1001;

                    \App\Models\Invoice::create([
                        'tenant_id' => $this->tenantId,
                        'order_id' => $order->id,
                        'customer_id' => $this->selectedCustomer->id,
                        'uuid' => \Illuminate\Support\Str::uuid()->toString(),
                        'series' => 'F',
                        'folio' => $nextFolio,
                        'status' => 'active',
                    ]);
                }
            });

            $this->clearCart();
            $this->showPaymentModal = false;
            $this->reset([
                'discountValue', 'discountReason', 'appliedDiscount', 'couponCode', 'appliedPromotions',
                'selectedCustomerId', 'selectedCustomer', 'customerQuery', 'customersList',
                'isPayingWithCredit', 'isPayingWithPoints', 'pointsToRedeem', 'pointsDiscountAmount', 'shouldInvoiceImmediate'
            ]);
            
            session()->flash('success', "¡Venta cobrada con éxito! El inventario ha sido actualizado en tiempo real.");
        } catch (\Exception $e) {
            session()->flash('error', "Error al procesar el pago: " . $e->getMessage());
        }
    }

    public function checkout()
    {
        return $this->processPayment();
    }

    public function openBox()
    {
        $this->validate([
            'openingAmount'   => 'required|numeric|min:0',
            'openingPassword' => 'required|string',
        ], [
            'openingPassword.required' => 'La contraseña es obligatoria para abrir la caja.',
        ]);

        // Verify the authenticated user's password
        $user = auth()->user();
        if (!$user || !Hash::check($this->openingPassword, $user->password)) {
            $this->addError('openingPassword', 'Contraseña incorrecta. Por favor, verifica e intenta de nuevo.');
            return;
        }

        try {
            $register = \App\Models\CashRegister::where('branch_id', $this->branchId)
                ->where('tenant_id', $this->tenantId)
                ->first();

            if (!$register) {
                session()->flash('error', 'No se encontró una caja registradora para esta sucursal.');
                return;
            }

            $action = new \App\Actions\OpenCashRegisterSessionAction();
            $session = $action->execute([
                'cash_register_id' => $register->id,
                'opened_by'        => auth()->id(),
                'opening_amount'   => $this->openingAmount,
                'notes'            => $this->openingNotes,
            ]);

            $this->cashRegisterSessionId = $session->id;
            $this->isBoxOpen = true;
            $this->showOpenBoxModal = false;

            session()->flash('success', "¡Caja abierta con éxito! Monto inicial: $" . number_format($this->openingAmount, 2));
            $this->reset(['openingAmount', 'openingNotes', 'openingPassword']);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al abrir caja: ' . $e->getMessage());
        }
    }

    public function prepareCloseBox()
    {
        if (!$this->cashRegisterSessionId) return;

        $session = \App\Models\CashRegisterSession::find($this->cashRegisterSessionId);
        if (!$session) return;

        // Calculate cash sales total
        $this->cashSalesTotal = Order::where('cash_register_session_id', $session->id)
            ->where('payment_method', 'efectivo')
            ->where('payment_status', 'paid')
            ->sum('total');

        // Calculate manual cash transactions
        $this->manualInflows = $session->transactions()->where('type', 'in')->sum('amount');
        $this->manualOutflows = $session->transactions()->where('type', 'out')->sum('amount');

        // Compute expected cash amount
        $this->expectedCash = $session->opening_amount + $this->cashSalesTotal + $this->manualInflows - $this->manualOutflows;
        $this->closingAmount = $this->expectedCash; // pre-populate with expected amount for convenience
        
        $this->showCloseBoxModal = true;
    }

    public function closeBox()
    {
        $this->validate([
            'closingAmount'   => 'required|numeric|min:0',
            'closingPassword' => 'required|string',
        ], [
            'closingPassword.required' => 'La contraseña es obligatoria para cerrar la caja.',
        ]);

        // Verify the authenticated user's password
        $user = auth()->user();
        if (!$user || !Hash::check($this->closingPassword, $user->password)) {
            $this->addError('closingPassword', 'Contraseña incorrecta. Por favor, verifica e intenta de nuevo.');
            return;
        }

        try {
            $action = new \App\Actions\CloseCashRegisterSessionAction();
            $action->execute([
                'session_id' => $this->cashRegisterSessionId,
                'closed_by' => auth()->id(),
                'closing_amount' => $this->closingAmount,
                'closing_notes' => $this->closingNotes,
            ]);

            $this->cashRegisterSessionId = null;
            $this->isBoxOpen = false;
            $this->showCloseBoxModal = false;

            session()->flash('success', "Caja cerrada correctamente. Reporte de diferencias guardado.");
            $this->reset(['closingAmount', 'closingNotes', 'closingPassword', 'cashSalesTotal', 'expectedCash', 'manualInflows', 'manualOutflows']);
        } catch (\Exception $e) {
            session()->flash('error', "Error al cerrar caja: " . $e->getMessage());
        }
    }

    public function registerTransaction()
    {
        $this->validate([
            'transactionAmount' => 'required|numeric|min:0.01',
            'transactionReason' => 'required|string|min:3',
        ]);

        try {
            $action = new \App\Actions\CreateCashTransactionAction();
            $action->execute([
                'cash_register_session_id' => $this->cashRegisterSessionId,
                'type' => $this->transactionType,
                'amount' => $this->transactionAmount,
                'reason' => $this->transactionReason,
            ]);

            $this->showTransactionModal = false;
            session()->flash('success', "Movimiento de caja registrado: " . ($this->transactionType === 'in' ? 'Ingreso' : 'Retiro') . " de $" . number_format($this->transactionAmount, 2));
            $this->reset(['transactionAmount', 'transactionReason', 'transactionType']);
        } catch (\Exception $e) {
            session()->flash('error', "Error al registrar movimiento: " . $e->getMessage());
        }
    }

    /**
     * Verificar si el celular vinculado ha escaneado algún código
     */
    public function checkMobileScans()
    {
        if (!$this->pairingToken) return;

        $scan = \App\Models\ScannerScan::where('pairing_token', $this->pairingToken)
            ->where('is_processed', false)
            ->first();

        if ($scan) {
            $this->addToCartByBarcode($scan->barcode);
            $scan->is_processed = true;
            $scan->save();
        }
    }

    /**
     * Agregar un producto al carrito localizándolo por código de barras o SKU
     */
    public function addToCartByBarcode($barcode)
    {
        $product = Product::where('tenant_id', $this->tenantId)
            ->where('status', 'active')
            ->where(function($q) use ($barcode) {
                $q->where('barcode', $barcode)
                  ->orWhere('sku', $barcode);
            })
            ->first();

        if ($product) {
            $this->addToCart($product->id);
            session()->flash('success', "¡'{$product->title}' agregado al carrito por código de barras!");
        } else {
            session()->flash('error', "No se encontró ningún producto con el código: {$barcode}");
        }
    }
}
