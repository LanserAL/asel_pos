<?php

namespace App\Actions\SuperAdmin;

use App\Models\Tenant;
use Illuminate\Support\Str;

class CreateTenantAction
{
    /**
     * Executes the creation of a new Tenant.
     */
    public function execute(array $data): Tenant
    {
        $slug = Str::slug($data['slug'] ?? $data['name']);
        
        return Tenant::create([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'status' => 'active',
            'plan_capacity' => [
                'max_branches' => $data['max_branches'] ?? 1,
                'max_users' => $data['max_users'] ?? 5,
                'max_products' => $data['max_products'] ?? 100,
            ],
            'currencies' => $data['currencies'] ?? ['MXN'],
            'expires_at' => now()->addYear(), // default to 1 year subscription
        ]);
    }
}
