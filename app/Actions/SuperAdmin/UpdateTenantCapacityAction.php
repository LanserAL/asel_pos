<?php

namespace App\Actions\SuperAdmin;

use App\Models\Tenant;

class UpdateTenantCapacityAction
{
    /**
     * Updates the JSON capacity for a tenant.
     */
    public function execute(Tenant $tenant, array $capacities): Tenant
    {
        $currentCapacity = $tenant->plan_capacity ?? [];
        
        $newCapacity = array_merge($currentCapacity, [
            'max_branches' => $capacities['max_branches'] ?? $currentCapacity['max_branches'] ?? 1,
            'max_users' => $capacities['max_users'] ?? $currentCapacity['max_users'] ?? 5,
            'max_products' => $capacities['max_products'] ?? $currentCapacity['max_products'] ?? 100,
        ]);

        $tenant->update([
            'plan_capacity' => $newCapacity
        ]);

        return $tenant;
    }
}
