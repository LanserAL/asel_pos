<?php

namespace App\Actions\SuperAdmin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateTenantUserAction
{
    /**
     * Executes the creation of a user assigned to a tenant.
     */
    public function execute(int $tenantId, array $data): User
    {
        // Use withoutGlobalScopes to avoid issues if the creator is superadmin
        $user = User::withoutGlobalScopes()->create([
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'vendedor',
        ]);

        // Assign Spatie Role
        $roleName = $data['role'] ?? 'vendedor';
        
        // Ensure the role exists before assigning, optionally create if needed.
        // For a robust system, roles should be seeded. 
        // We will assign it if the spatie package is active.
        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $user->assignRole($roleName);
        }

        return $user;
    }
}
