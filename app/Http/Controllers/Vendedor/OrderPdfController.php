<?php
 
 namespace App\Http\Controllers\Vendedor;
 
 use App\Http\Controllers\Controller;
 use App\Models\Order;
 use App\Models\Branch;
 use Illuminate\Http\Request;
 use Barryvdh\DomPDF\Facade\Pdf;
 use Illuminate\Support\Facades\Auth;
 
 class OrderPdfController extends Controller
 {
     /**
      * Download consolidated sales report PDF.
      */
     public function downloadSalesReport(Request $request)
     {
         $user = Auth::user();
         if (!$user || !$user->tenant_id) {
             abort(403, 'No autorizado.');
         }
 
         $query = Order::with(['branch', 'user']);
 
         // Aplicar filtros similares a los de la vista de administración
         if ($request->filled('search')) {
             $search = $request->search;
             $query->where(function($q) use ($search) {
                 $q->where('customer_name_manual', 'like', "%{$search}%")
                   ->orWhere('id', 'like', "%{$search}%");
             });
         }
 
         if ($request->filled('branch_id')) {
             $query->where('branch_id', $request->branch_id);
         }
 
         if ($request->filled('payment_method')) {
             $query->where('payment_method', $request->payment_method);
         }
 
         if ($request->filled('payment_status')) {
             $query->where('payment_status', $request->payment_status);
         }
 
         if ($request->filled('delivery_status')) {
             $query->where('delivery_status', $request->delivery_status);
         }
 
         if ($request->filled('source')) {
             $query->where('source', $request->source);
         }
 
         // Filtro de fechas
         if ($request->filled('start_date') && $request->filled('end_date')) {
             $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
         } elseif ($request->filled('date_range')) {
             $range = $request->date_range;
             if ($range === 'today') {
                 $query->whereDate('created_at', now()->today());
             } elseif ($range === 'yesterday') {
                 $query->whereDate('created_at', now()->yesterday());
             } elseif ($range === 'week') {
                 $query->where('created_at', '>=', now()->subDays(7));
             } elseif ($range === 'month') {
                 $query->whereMonth('created_at', now()->month)
                       ->whereYear('created_at', now()->year);
             }
         }
 
         $orders = $query->latest()->get();
         
         // Calcular métricas
         $totalRevenue = $orders->sum('total');
         $totalCost = $orders->sum('subtotal'); // Simplificado
         $ordersCount = $orders->count();
         $avgTicket = $ordersCount > 0 ? $totalRevenue / $ordersCount : 0;
 
         $branchStats = [];
         $paymentStats = [];
 
         foreach ($orders as $order) {
             $branchName = $order->branch->name ?? 'Sin sucursal';
             $branchStats[$branchName] = ($branchStats[$branchName] ?? 0) + $order->total;
 
             $method = ucfirst($order->payment_method);
             $paymentStats[$method] = ($paymentStats[$method] ?? 0) + $order->total;
         }
 
         $tenantName = $user->tenant->name ?? 'ASEL POS';
 
         // Cargar vista PDF
         $pdf = Pdf::loadView('pdf.sales-report', [
             'orders' => $orders,
             'totalRevenue' => $totalRevenue,
             'ordersCount' => $ordersCount,
             'avgTicket' => $avgTicket,
             'branchStats' => $branchStats,
             'paymentStats' => $paymentStats,
             'tenantName' => $tenantName,
             'startDate' => $request->start_date ?? now()->subDays(7)->format('Y-m-d'),
             'endDate' => $request->end_date ?? now()->format('Y-m-d')
         ]);
 
         return $pdf->download('Reporte_Ventas_ASELPOS_' . now()->format('Y-m-d') . '.pdf');
     }
 
     /**
      * Download thermal-style ticket PDF.
      */
     public function downloadTicket(Order $order)
     {
         $user = Auth::user();
         if (!$user || !$user->tenant_id || $order->tenant_id !== $user->tenant_id) {
             abort(403, 'No autorizado.');
         }
 
         // Load relations for ticket detailing
         $order->load(['items.product', 'branch', 'user']);
 
         $tenantName = $user->tenant->name ?? 'ASEL POS';
         $branchName = $order->branch->name ?? 'Sucursal Centro';
         $branchAddress = $order->branch->address ?? '';
         $branchPhone = $order->branch->phone ?? '';
 
         $pdf = Pdf::loadView('pdf.ticket', [
             'order' => $order,
             'tenantName' => $tenantName,
             'branchName' => $branchName,
             'branchAddress' => $branchAddress,
             'branchPhone' => $branchPhone
         ]);
         
         // Ajustar el tamaño del papel para simular ticket digital A4/Letter
         $pdf->setPaper('letter', 'portrait');
 
         return $pdf->download('Ticket_Venta_' . $order->id . '.pdf');
     }
 }
