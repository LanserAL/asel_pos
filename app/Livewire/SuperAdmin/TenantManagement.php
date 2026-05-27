<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant;
use App\Models\Branch;
use App\Models\User;
use App\Actions\SuperAdmin\CreateTenantAction;
use App\Actions\SuperAdmin\CreateTenantUserAction;
use App\Actions\SuperAdmin\UpdateTenantCapacityAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantManagement extends Component
{
    use WithPagination;

    public $search = '';

    public $systemCurrencies = ['MXN', 'USD', 'EUR', 'COP'];

    // Create Tenant Form Properties
    public $newTenantName = '';
    public $newTenantSlug = '';
    public $newTenantEmail = '';
    public $newTenantPassword = '';
    public $newTenantMaxBranches = 1;
    public $newTenantMaxProducts = 500;
    public $newTenantMaxUsers = 5;
    public $newTenantCurrencies = ['MXN'];
    public $tempPassword = '';

    // Edit Tenant Form Properties
    public $editTenantId = null;
    public $editTenantName = '';
    public $editTenantSlug = '';
    public $editTenantCurrencies = [];
    public $editTenantAiProvider = '';
    public $editTenantAiApiKey = '';
    public $editTenantAiModel = '';

    // Capacity Update Form Properties
    public $capTenantId = null;
    public $capMaxBranches = 1;
    public $capMaxProducts = 500;
    public $capMaxUsers = 5;

    // Create Employee Form Properties
    public $empTenantId = null;
    public $empBranchId = null;
    public $empName = '';
    public $empEmail = '';
    public $empPassword = '';
    public $tenantBranches = [];

    // Manage Tenant (Tabs) Properties
    public $manageTenantId = null;
    public $manageTenantName = '';
    public $manageUsers = [];
    public $manageBranches = [];
    public $manageActiveTab = 'users';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function createTenant(CreateTenantAction $createTenantAction, CreateTenantUserAction $createTenantUserAction)
    {
        $this->validate([
            'newTenantName' => 'required|string|max:255',
            'newTenantSlug' => 'required|string|max:255|unique:tenants,slug',
            'newTenantEmail' => 'required|email|unique:users,email',
            'newTenantPassword' => 'required|min:8',
            'newTenantMaxBranches' => 'required|integer|min:1',
            'newTenantMaxProducts' => 'required|integer|min:1',
            'newTenantMaxUsers' => 'required|integer|min:1',
            'newTenantCurrencies' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $tenant = $createTenantAction->execute([
                'name' => $this->newTenantName,
                'slug' => $this->newTenantSlug,
                'max_branches' => $this->newTenantMaxBranches,
                'max_products' => $this->newTenantMaxProducts,
                'max_users' => $this->newTenantMaxUsers,
                'currencies' => $this->newTenantCurrencies,
            ]);

            $createTenantUserAction->execute($tenant->id, [
                'name' => 'Admin ' . $this->newTenantName,
                'email' => $this->newTenantEmail,
                'password' => $this->newTenantPassword,
                'role' => 'admin',
            ]);

            DB::commit();
            session()->flash('message', 'Tienda y usuario registrados exitosamente.');
            $this->tempPassword = $this->newTenantPassword;
            $this->reset(['newTenantName', 'newTenantSlug', 'newTenantEmail', 'newTenantPassword', 'newTenantMaxBranches', 'newTenantMaxProducts', 'newTenantMaxUsers']);
            $this->newTenantCurrencies = ['MXN'];
            $this->dispatch('close-modal');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al crear la tienda: ' . $e->getMessage());
        }
    }

    public function toggleTenantStatus($tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);
        $tenant->status = $tenant->status === 'active' ? 'suspended' : 'active';
        $tenant->save();
        session()->flash('message', 'Estado de tienda actualizado.');
    }

    public function loadEditTenant($id)
    {
        $tenant = Tenant::findOrFail($id);
        $this->editTenantId = $tenant->id;
        $this->editTenantName = $tenant->name;
        $this->editTenantSlug = $tenant->slug;
        $this->editTenantCurrencies = $tenant->currencies ?? ['MXN'];
        $this->editTenantAiProvider = $tenant->ai_provider ?? '';
        $this->editTenantAiApiKey = $tenant->ai_api_key ?? '';
        $this->editTenantAiModel = $tenant->ai_model ?? '';
    }

    public function updateTenant()
    {
        $this->validate([
            'editTenantName' => 'required|string|max:255',
            'editTenantSlug' => 'required|string|max:255|unique:tenants,slug,' . $this->editTenantId,
            'editTenantCurrencies' => 'required|array|min:1',
            'editTenantAiProvider' => 'nullable|string|in:gemini,openai,claude',
            'editTenantAiApiKey' => 'nullable|string',
            'editTenantAiModel' => 'nullable|string',
        ]);

        $tenant = Tenant::findOrFail($this->editTenantId);
        $tenant->update([
            'name' => $this->editTenantName,
            'slug' => $this->editTenantSlug,
            'currencies' => $this->editTenantCurrencies,
            'ai_provider' => $this->editTenantAiProvider ?: null,
            'ai_api_key' => $this->editTenantAiApiKey ?: null,
            'ai_model' => $this->editTenantAiModel ?: null,
        ]);

        session()->flash('message', 'Datos de tienda y configuración IA actualizados.');
        $this->dispatch('close-modal');
    }

    public function loadCapacity($id)
    {
        $tenant = Tenant::findOrFail($id);
        $this->capTenantId = $tenant->id;
        $this->capMaxBranches = $tenant->plan_capacity['max_branches'] ?? 1;
        $this->capMaxProducts = $tenant->plan_capacity['max_products'] ?? 100;
        $this->capMaxUsers = $tenant->plan_capacity['max_users'] ?? 5;
    }

    public function updateCapacity(UpdateTenantCapacityAction $updateAction)
    {
        $this->validate([
            'capMaxBranches' => 'required|integer|min:1',
            'capMaxProducts' => 'required|integer|min:1',
            'capMaxUsers' => 'required|integer|min:1',
        ]);

        $tenant = Tenant::findOrFail($this->capTenantId);
        $updateAction->execute($tenant, [
            'max_branches' => $this->capMaxBranches,
            'max_products' => $this->capMaxProducts,
            'max_users' => $this->capMaxUsers,
        ]);

        session()->flash('message', 'Límites del plan actualizados.');
        $this->dispatch('close-modal');
    }

    public function loadEmployeeForm($tenantId)
    {
        $this->empTenantId = $tenantId;
        $this->tenantBranches = Branch::where('tenant_id', $tenantId)->get();
        $this->reset(['empBranchId', 'empName', 'empEmail', 'empPassword']);
        $this->resetValidation();
    }

    public function saveEmployee()
    {
        $this->validate([
            'empBranchId' => 'required|exists:branches,id',
            'empName' => 'required|string|max:255',
            'empEmail' => 'required|email|unique:users,email',
            'empPassword' => 'required|string|min:6',
        ]);

        $tenant = Tenant::findOrFail($this->empTenantId);
        $vendedorCount = User::where('tenant_id', $tenant->id)->count();
        $maxUsers = $tenant->plan_capacity['max_users'] ?? 10;

        if ($vendedorCount >= $maxUsers) {
            session()->flash('error', "Límite de usuarios alcanzado para esta tienda ({$maxUsers}).");
            return;
        }

        $vendedor = User::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $this->empBranchId,
            'name' => $this->empName,
            'email' => $this->empEmail,
            'password' => Hash::make($this->empPassword),
            'role' => 'vendedor',
        ]);

        $vendedor->assignRole('vendedor');

        session()->flash('message', 'Vendedor registrado exitosamente para la tienda.');
        $this->dispatch('close-modal');
    }

    public function openManageModal($tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);
        $this->manageTenantId = $tenant->id;
        $this->manageTenantName = $tenant->name;
        $this->manageActiveTab = 'users';
        $this->loadManageData();
    }

    public function setManageTab($tab)
    {
        $this->manageActiveTab = $tab;
    }

    public function loadManageData()
    {
        $this->manageUsers = User::where('tenant_id', $this->manageTenantId)->get();
        $this->manageBranches = Branch::where('tenant_id', $this->manageTenantId)->get();
    }

    public function toggleManageBranchStatus($branchId)
    {
        $branch = Branch::findOrFail($branchId);
        if ($branch->tenant_id == $this->manageTenantId) {
            $branch->status = $branch->status === 'active' ? 'inactive' : 'active';
            $branch->save();
            $this->loadManageData();
            session()->flash('manage_message', 'Estado de la sucursal actualizado.');
        }
    }

    public function deleteManageBranch($branchId)
    {
        $branch = Branch::findOrFail($branchId);
        if ($branch->tenant_id == $this->manageTenantId) {
            $branch->delete();
            $this->loadManageData();
            session()->flash('manage_message', 'Sucursal eliminada exitosamente.');
        }
    }

    public function deleteManageUser($userId)
    {
        $user = User::findOrFail($userId);
        if ($user->tenant_id == $this->manageTenantId) {
            $user->delete();
            $this->loadManageData();
            session()->flash('manage_message', 'Usuario eliminado exitosamente.');
        }
    }

    public function render()
    {
        $tenants = Tenant::where(function($query) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('slug', 'like', '%' . $this->search . '%');
        })
        ->withCount('users')
        ->latest()
        ->paginate(10);

        return view('livewire.super-admin.tenant-management', [
            'tenants' => $tenants,
        ]);
    }
}
