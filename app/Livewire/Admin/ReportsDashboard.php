<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\Branch;
use App\Models\CashRegisterSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ReportsDashboard extends Component
{
    public $startDate;
    public $endDate;

    public function mount()
    {
        // Default to current month
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $tenantId = Auth::user()->tenant_id;
        $start = $this->startDate . ' 00:00:00';
        $end = $this->endDate . ' 23:59:59';

        // 1. Fetch Orders within range
        $orders = Order::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$start, $end])
            ->with(['items.product', 'branch'])
            ->get();

        $totalRevenue = $orders->sum('total');
        $ordersCount = $orders->count();
        $avgTicket = $ordersCount > 0 ? ($totalRevenue / $ordersCount) : 0;

        // Calculate Cost & Profit
        $totalCost = 0;
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $productCost = $item->product ? ($item->product->cost ?: 0) : 0;
                $totalCost += $item->quantity * $productCost;
            }
        }
        $totalProfit = $totalRevenue - $totalCost;

        // 2. Payments stats
        $paymentStats = [];
        $paymentGroups = Order::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$start, $end])
            ->select('payment_method', DB::raw('SUM(total) as total_amount'))
            ->groupBy('payment_method')
            ->get();

        foreach ($paymentGroups as $pg) {
            $paymentStats[ucfirst($pg->payment_method)] = $pg->total_amount;
        }

        // 3. Sales per Branch stats
        $branchSales = Order::where('orders.tenant_id', $tenantId)
            ->whereBetween('orders.created_at', [$start, $end])
            ->join('branches', 'orders.branch_id', '=', 'branches.id')
            ->select('branches.name as name', DB::raw('SUM(orders.total) as total'))
            ->groupBy('branches.id', 'branches.name')
            ->get();

        // 4. Top Products stats
        $topProducts = \App\Models\OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.tenant_id', $tenantId)
            ->whereBetween('orders.created_at', [$start, $end])
            ->select('order_items.product_name_backup as name', DB::raw('SUM(order_items.quantity) as quantity'), DB::raw('SUM(order_items.total) as revenue'))
            ->groupBy('order_items.product_id', 'order_items.product_name_backup')
            ->orderBy('quantity', 'desc')
            ->take(5)
            ->get();

        // 5. Cash Register Sessions history
        $sessions = CashRegisterSession::join('cash_registers', 'cash_register_sessions.cash_register_id', '=', 'cash_registers.id')
            ->join('users', 'cash_register_sessions.opened_by', '=', 'users.id')
            ->join('branches', 'cash_registers.branch_id', '=', 'branches.id')
            ->where('cash_registers.tenant_id', $tenantId)
            ->whereBetween('cash_register_sessions.created_at', [$start, $end])
            ->select(
                'cash_register_sessions.*',
                'users.name as user_name',
                'cash_registers.name as register_name',
                'branches.name as branch_name'
            )
            ->latest()
            ->get();

        return view('livewire.admin.reports-dashboard', [
            'totalRevenue' => $totalRevenue,
            'ordersCount' => $ordersCount,
            'avgTicket' => $avgTicket,
            'totalProfit' => $totalProfit,
            'totalCost' => $totalCost,
            'paymentStats' => $paymentStats,
            'branchSales' => $branchSales,
            'topProducts' => $topProducts,
            'sessions' => $sessions
        ])->layout('components.layouts.app');
    }

    public function downloadAuditReport()
    {
        $tenantId = Auth::user()->tenant_id;
        $tenant = Auth::user()->tenant;
        $tenantName = $tenant ? $tenant->name : 'ASEL POS';
        $logo = $tenant && $tenant->logo_path ? $tenant->logo_path : '';

        $start = $this->startDate . ' 00:00:00';
        $end = $this->endDate . ' 23:59:59';

        $sessions = CashRegisterSession::join('cash_registers', 'cash_register_sessions.cash_register_id', '=', 'cash_registers.id')
            ->join('users', 'cash_register_sessions.opened_by', '=', 'users.id')
            ->join('branches', 'cash_registers.branch_id', '=', 'branches.id')
            ->where('cash_registers.tenant_id', $tenantId)
            ->whereBetween('cash_register_sessions.created_at', [$start, $end])
            ->select(
                'cash_register_sessions.*',
                'users.name as user_name',
                'cash_registers.name as register_name',
                'branches.name as branch_name'
            )
            ->latest()
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.audit-report', [
            'sessions' => $sessions,
            'tenantName' => $tenantName,
            'logo' => $logo,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Reporte_Auditoria_ASELPOS_' . now()->format('Y-m-d') . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
