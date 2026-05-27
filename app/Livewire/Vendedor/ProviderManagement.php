<?php

namespace App\Livewire\Vendedor;

use App\Models\Provider;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ProviderManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $name, $company, $email, $phone, $address, $status = 'active';
    public $providerId = null;

    protected $rules = [
        'name' => 'required|string|max:255',
        'company' => 'nullable|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string',
        'status' => 'required|in:active,inactive',
    ];

    public function mount()
    {
        if (!Auth::check() || !Auth::user()->tenant_id) {
            abort(403, 'Acceso denegado.');
        }
    }

    public function saveProvider()
    {
        $this->validate();

        Provider::updateOrCreate(
            ['id' => $this->providerId, 'tenant_id' => Auth::user()->tenant_id],
            [
                'name' => $this->name,
                'company' => $this->company,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
                'status' => $this->status,
            ]
        );

        session()->flash('message', $this->providerId ? 'Proveedor actualizado exitosamente.' : 'Proveedor registrado exitosamente.');
        
        $this->resetForm();
        $this->dispatch('close-modal');
    }

    public function editProvider($id)
    {
        $provider = Provider::findOrFail($id);
        $this->providerId = $provider->id;
        $this->name = $provider->name;
        $this->company = $provider->company;
        $this->email = $provider->email;
        $this->phone = $provider->phone;
        $this->address = $provider->address;
        $this->status = $provider->status;
    }

    public function resetForm()
    {
        $this->reset(['name', 'company', 'email', 'phone', 'address', 'status', 'providerId']);
        $this->resetValidation();
    }

    public function toggleStatus($id)
    {
        $provider = Provider::findOrFail($id);
        $provider->update(['status' => $provider->status === 'active' ? 'inactive' : 'active']);
        session()->flash('message', 'Estado de proveedor actualizado.');
    }

    public function render()
    {
        $providers = Provider::where(function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('company', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.vendedor.provider-management', [
            'providers' => $providers
        ])->layout('components.layouts.app');
    }
}
