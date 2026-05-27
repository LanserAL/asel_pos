<?php
 
 namespace App\Livewire\Vendedor;
 
 use Livewire\Component;
 use Livewire\WithPagination;
 use App\Models\Order;
 use App\Models\Branch;
 use Illuminate\Support\Facades\Auth;
 
 class OrderManagement extends Component
 {
     use WithPagination;
 
     public $search = '';
     public $branchIdFilter = '';
     public $paymentMethodFilter = '';
     public $paymentStatusFilter = '';
     public $deliveryStatusFilter = '';
     public $sourceFilter = '';
     
     // Filtros de fecha
     public $dateRange = 'week'; // Por defecto, última semana
     public $startDate = '';
     public $endDate = '';
     
     // Para visualización de detalles
     public $selectedOrderId = null;
     public $selectedOrder = null;

     // Reprint Audit properties
     public $reprintReason = '';
     public $reprintType = 'full';
     public $ticketReprintOrderId = null;
 
     protected $queryString = [
         'search' => ['except' => ''],
         'branchIdFilter' => ['except' => ''],
         'paymentMethodFilter' => ['except' => ''],
         'paymentStatusFilter' => ['except' => ''],
         'deliveryStatusFilter' => ['except' => ''],
         'sourceFilter' => ['except' => ''],
         'dateRange' => ['except' => 'week'],
     ];
 
     public function mount()
     {
         if (!Auth::check() || !Auth::user()->tenant_id) {
             abort(403, 'Acceso denegado.');
         }
 
         if (Auth::user()->hasRole('vendedor') && Auth::user()->branch_id) {
             $this->branchIdFilter = Auth::user()->branch_id;
         }
 
         $this->startDate = now()->subDays(7)->format('Y-m-d');
         $this->endDate = now()->format('Y-m-d');
     }
 
     public function selectOrder($orderId)
     {
         $this->selectedOrderId = $orderId;
         $this->selectedOrder = Order::with(['branch', 'user', 'items.product'])->findOrFail($orderId);
         $this->dispatch('show-order-details');
     }
 
     public function closeDetails()
     {
         $this->selectedOrderId = null;
         $this->selectedOrder = null;
     }
 
     public function togglePaymentStatus($orderId)
     {
         $order = Order::findOrFail($orderId);
         $order->payment_status = $order->payment_status === 'paid' ? 'pending' : 'paid';
         $order->save();
 
         session()->flash('message', 'Estado de pago actualizado exitosamente.');
         if ($this->selectedOrderId == $orderId) {
             $this->selectOrder($orderId);
         }
     }
 
     public function updateDeliveryStatus($orderId, $status)
     {
         $allowed = ['pendiente', 'preparando', 'enviado', 'entregado'];
         if (!in_array($status, $allowed)) {
             return;
         }
 
         $order = Order::findOrFail($orderId);
         $order->delivery_status = $status;
         $order->save();
 
         session()->flash('message', 'Estado de entrega actualizado exitosamente.');
         if ($this->selectedOrderId == $orderId) {
             $this->selectOrder($orderId);
         }
     }
 
     public function updatedDateRange($value)
     {
         if ($value === 'today') {
             $this->startDate = now()->format('Y-m-d');
             $this->endDate = now()->format('Y-m-d');
         } elseif ($value === 'yesterday') {
             $this->startDate = now()->yesterday()->format('Y-m-d');
             $this->endDate = now()->yesterday()->format('Y-m-d');
         } elseif ($value === 'week') {
             $this->startDate = now()->subDays(7)->format('Y-m-d');
             $this->endDate = now()->format('Y-m-d');
         } elseif ($value === 'month') {
             $this->startDate = now()->startOfMonth()->format('Y-m-d');
             $this->endDate = now()->endOfMonth()->format('Y-m-d');
         }
         $this->resetPage();
     }
 
     public function clearFilters()
     {
         $this->reset([
             'search', 'paymentMethodFilter',
             'paymentStatusFilter', 'deliveryStatusFilter', 'sourceFilter', 'dateRange'
         ]);
         
         if (!Auth::user()->hasRole('vendedor')) {
             $this->reset(['branchIdFilter']);
         }
         
         $this->startDate = now()->subDays(7)->format('Y-m-d');
         $this->endDate = now()->format('Y-m-d');
         $this->resetPage();
     }
 
     public function render()
     {
         $user = Auth::user();
         if ($user->hasRole('vendedor') && $user->branch_id) {
             $branches = Branch::where('id', $user->branch_id)->get();
             $this->branchIdFilter = $user->branch_id;
         } else {
             $branches = Branch::all();
         }
 
         $query = Order::with(['branch', 'user']);
 
         if ($this->search) {
             $query->where(function($q) {
                 $q->where('customer_name_manual', 'like', '%' . $this->search . '%')
                   ->orWhere('id', 'like', '%' . $this->search . '%')
                   ->orWhere('customer_phone', 'like', '%' . $this->search . '%');
             });
         }
 
         if ($this->branchIdFilter) {
             $query->where('branch_id', $this->branchIdFilter);
         }
 
         if ($this->paymentMethodFilter) {
             $query->where('payment_method', $this->paymentMethodFilter);
         }
 
         if ($this->paymentStatusFilter) {
             $query->where('payment_status', $this->paymentStatusFilter);
         }
 
         if ($this->deliveryStatusFilter) {
             $query->where('delivery_status', $this->deliveryStatusFilter);
         }
 
         if ($this->sourceFilter) {
             $query->where('source', $this->sourceFilter);
         }
 
         // Filtro de fechas
         if ($this->startDate && $this->endDate) {
             $query->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
         }
 
         $orders = $query->latest()->paginate(10);
 
         // Totales rápidos para los KPIs superiores
         $totalRevenueFiltered = $query->clone()->where('payment_status', 'paid')->sum('total');
         $pendingRevenueFiltered = $query->clone()->where('payment_status', 'pending')->sum('total');
         $totalOrdersFiltered = $query->clone()->count();
 
         return view('livewire.vendedor.order-management', [
             'orders' => $orders,
             'branches' => $branches,
             'totalRevenueFiltered' => $totalRevenueFiltered,
             'pendingRevenueFiltered' => $pendingRevenueFiltered,
             'totalOrdersFiltered' => $totalOrdersFiltered
         ])->layout('components.layouts.app');
     }

     public function reprintOrder($orderId)
     {
         $this->validate([
             'reprintReason' => 'required|string|min:5',
             'reprintType' => 'required|string',
         ], [
             'reprintReason.required' => 'El motivo de reimpresión es obligatorio.',
             'reprintReason.min' => 'El motivo debe tener al menos 5 caracteres.',
         ]);

         try {
             $action = new \App\Actions\ReprintTicketAction();
             $action->execute([
                 'order_id' => $orderId,
                 'user_id' => Auth::id(),
                 'reason' => $this->reprintReason,
                 'type' => $this->reprintType,
             ]);

             session()->flash('message', 'Reimpresión autorizada y registrada en la auditoría con éxito.');

             // Reset fields
             $this->reset(['reprintReason', 'reprintType']);
            
             // Dispatch browser event to download the PDF
             $this->dispatch('trigger-pdf-download', orderId: $orderId);
         } catch (\Exception $e) {
             session()->flash('error', 'Error al autorizar reimpresión: ' . $e->getMessage());
         }
     }
 }
