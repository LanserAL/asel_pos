<?php

namespace App\Livewire\Vendedor;

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class BranchManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $name, $address, $phone, $status = 'active';
    public $branchId = null;
    public $currency = 'MXN';
    public $allowedCurrencies = [];

    public $empName, $empEmail, $empPassword;
    public $selectedBranchId = null;

    public $branchEmployees = [];
    public $viewingBranchName = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'address' => 'nullable|string',
        'phone' => 'nullable|string|max:20',
        'status' => 'required|in:active,inactive',
        'currency' => 'required|string',
    ];

    public function mount()
    {
        // Enforced by middleware, but good to ensure
        if (!Auth::check() || !Auth::user()->tenant_id) {
            abort(403, 'Acceso denegado.');
        }

        $tenant = Tenant::find(Auth::user()->tenant_id);
        $this->allowedCurrencies = $tenant->currencies ?? ['MXN'];
        $this->currency = $this->allowedCurrencies[0] ?? 'MXN';
    }

    public function saveBranch()
    {
        $this->validate();

        $tenant = Tenant::find(Auth::user()->tenant_id);
        $maxBranches = $tenant->plan_capacity['max_branches'] ?? 1;
        $currentBranches = Branch::count(); // Scoped to tenant automatically!

        if (!$this->branchId && $currentBranches >= $maxBranches) {
            session()->flash('error', "Has alcanzado el límite máximo de sucursales ({$maxBranches}) de tu plan.");
            return;
        }

        Branch::updateOrCreate(
            ['id' => $this->branchId, 'tenant_id' => $tenant->id],
            [
                'name' => $this->name,
                'address' => $this->address,
                'phone' => $this->phone,
                'status' => $this->status,
                'currency' => $this->currency,
            ]
        );

        session()->flash('message', $this->branchId ? 'Sucursal actualizada exitosamente.' : 'Sucursal registrada exitosamente.');
        
        $this->reset(['name', 'address', 'phone', 'status', 'branchId']);
        $this->currency = $this->allowedCurrencies[0] ?? 'MXN';
        $this->dispatch('close-modal');
    }

    public function editBranch($id)
    {
        $branch = Branch::findOrFail($id);
        $this->branchId = $branch->id;
        $this->name = $branch->name;
        $this->address = $branch->address;
        $this->phone = $branch->phone;
        $this->status = $branch->status;
        $this->currency = $branch->currency ?? ($this->allowedCurrencies[0] ?? 'MXN');
    }

    public function resetForm()
    {
        $this->reset(['name', 'address', 'phone', 'status', 'branchId']);
        $this->currency = $this->allowedCurrencies[0] ?? 'MXN';
        $this->resetValidation();
    }

    public function toggleStatus($id)
    {
        $branch = Branch::findOrFail($id);
        $branch->update(['status' => $branch->status === 'active' ? 'inactive' : 'active']);
        session()->flash('message', 'Estado de sucursal actualizado.');
    }

    public function openEmployeeModal($branchId)
    {
        $this->selectedBranchId = $branchId;
        $this->reset(['empName', 'empEmail', 'empPassword']);
        $this->resetValidation();
    }

    public function saveEmployee()
    {
        $this->validate([
            'empName' => 'required|string|max:255',
            'empEmail' => 'required|email|unique:users,email',
            'empPassword' => 'required|string|min:6',
        ]);

        $tenant_id = Auth::user()->tenant_id;
        $tenant = Tenant::find($tenant_id);

        $vendedorCount = User::where('tenant_id', $tenant_id)->count();
        $maxUsers = $tenant->plan_capacity['max_users'] ?? 10;

        if ($vendedorCount >= $maxUsers) {
            session()->flash('error', "Límite de usuarios alcanzado ({$maxUsers}).");
            return;
        }

        $vendedor = User::create([
            'tenant_id' => $tenant_id,
            'branch_id' => $this->selectedBranchId,
            'name' => $this->empName,
            'email' => $this->empEmail,
            'password' => Hash::make($this->empPassword),
            'role' => 'vendedor',
        ]);

        $vendedor->assignRole('vendedor');

        session()->flash('message', 'Empleado registrado exitosamente.');
        $this->dispatch('close-modal');
    }

    public function viewEmployees($branchId)
    {
        $branch = Branch::findOrFail($branchId);
        $this->viewingBranchName = $branch->name;
        $this->branchEmployees = User::where('branch_id', $branchId)->get();
    }

    public function removeEmployee($userId)
    {
        $user = User::findOrFail($userId);
        if ($user->tenant_id == Auth::user()->tenant_id) {
            $branchId = $user->branch_id;
            $user->delete();
            $this->branchEmployees = User::where('branch_id', $branchId)->get();
            session()->flash('message', 'Empleado eliminado exitosamente.');
        }
    }

    public function render()
    {
        $branches = Branch::where('name', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.vendedor.branch-management', [
            'branches' => $branches
        ])->layout('components.layouts.app');
    }
}
