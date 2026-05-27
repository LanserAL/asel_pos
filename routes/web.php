<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\SuperAdminDashboard;
use App\Livewire\SuperAdmin\TenantManagement;
use App\Livewire\VendedorDashboard;
use App\Livewire\TerminalPos;
use App\Livewire\CatalogoOnline;
use App\Livewire\AuthLogin;
use Illuminate\Support\Facades\Auth;

use App\Livewire\Vendedor\OrderManagement;
use App\Http\Controllers\Vendedor\OrderPdfController;
use App\Http\Controllers\ScannerController;

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->role === 'admin' && is_null($user->tenant_id)) {
            return redirect('/super-admin');
        }
        if ($user->hasRole('admin')) {
            return redirect('/admin');
        }
        return redirect('/vendedor');
    }
    return redirect('/login');
});

Route::get('/login', AuthLogin::class)->name('login')->middleware('guest');
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// Rutas públicas de Escaneo Celular, Kiosco y Seguimiento
Route::get('/scanner/{token}', [ScannerController::class, 'showMobileScanner'])->name('scanner.mobile');
Route::post('/api/scanner/scan', [ScannerController::class, 'receiveScan'])->name('api.scanner.scan');
Route::get('/kiosko', \App\Livewire\Kiosko::class)->name('kiosko');
Route::get('/orders/tracking/{orderId?}', \App\Livewire\OrderTracking::class)->name('orders.tracking');

Route::middleware(['auth'])->group(function () {
    Route::get('/super-admin', SuperAdminDashboard::class)->name('super-admin');
    Route::get('/super-admin/tenants', TenantManagement::class)->name('super-admin.tenants');
    
    // Rutas de Admin de Tienda
    Route::prefix('admin')->middleware('tenant.active')->group(function () {
        Route::get('/', VendedorDashboard::class)->name('admin.dashboard');
        Route::get('/branches', \App\Livewire\Vendedor\BranchManagement::class)->name('admin.branches');
        Route::get('/providers', \App\Livewire\Vendedor\ProviderManagement::class)->name('admin.providers');
        Route::get('/settings', \App\Livewire\Vendedor\Settings::class)->name('admin.settings');
        Route::get('/products', \App\Livewire\Vendedor\ProductManagement::class)->name('admin.products');
        Route::get('/promotions', \App\Livewire\Vendedor\PromotionManagement::class)->name('admin.promotions');
        Route::get('/inventory', \App\Livewire\Vendedor\InventoryManagement::class)->name('admin.inventory');
        Route::get('/orders', OrderManagement::class)->name('admin.orders');
        Route::get('/orders/report/pdf', [OrderPdfController::class, 'downloadSalesReport'])->name('admin.orders.report.pdf');
        Route::get('/orders/{order}/ticket/pdf', [OrderPdfController::class, 'downloadTicket'])->name('admin.orders.ticket.pdf');
        Route::get('/customers', \App\Livewire\Admin\CustomerManagement::class)->name('admin.customers');
        Route::get('/reports', \App\Livewire\Admin\ReportsDashboard::class)->name('admin.reports');
    });

    // Rutas de Vendedor
    Route::prefix('vendedor')->middleware('tenant.active')->group(function () {
        Route::get('/', VendedorDashboard::class)->name('vendedor.dashboard');
        Route::get('/branches', \App\Livewire\Vendedor\BranchManagement::class)->name('vendedor.branches');
        Route::get('/providers', \App\Livewire\Vendedor\ProviderManagement::class)->name('vendedor.providers');
        Route::get('/settings', \App\Livewire\Vendedor\Settings::class)->name('vendedor.settings');
        Route::get('/products', \App\Livewire\Vendedor\ProductManagement::class)->name('vendedor.products');
        Route::get('/inventory', \App\Livewire\Vendedor\InventoryManagement::class)->name('vendedor.inventory');
        Route::get('/orders', OrderManagement::class)->name('vendedor.orders');
        Route::get('/orders/report/pdf', [OrderPdfController::class, 'downloadSalesReport'])->name('vendedor.orders.report.pdf');
        Route::get('/orders/{order}/ticket/pdf', [OrderPdfController::class, 'downloadTicket'])->name('vendedor.orders.ticket.pdf');
        Route::get('/customers', \App\Livewire\Admin\CustomerManagement::class)->name('vendedor.customers');
    });

    Route::get('/pos', TerminalPos::class)->name('pos')->middleware('tenant.active');
});

Route::get('/catalogo', CatalogoOnline::class)->name('catalogo');
