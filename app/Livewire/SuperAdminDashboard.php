<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant;

class SuperAdminDashboard extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Fetch metrics for the dashboard view instead of paginated tenants
        $totalRevenue = 0; // Implement actual logic when billing is ready
        $activeTenantsCount = Tenant::where('status', 'active')->count();
        $expiringSubscriptions = 0; // Implement actual logic when subscriptions exist

        return view('livewire.super-admin-dashboard', [
            'totalRevenue' => $totalRevenue,
            'activeTenantsCount' => $activeTenantsCount,
            'expiringSubscriptions' => $expiringSubscriptions,
        ]);
    }
}
