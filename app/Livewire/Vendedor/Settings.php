<?php

namespace App\Livewire\Vendedor;

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

use Livewire\WithFileUploads;

class Settings extends Component
{
    use WithFileUploads;

    public $name;
    public $description;
    
    // Logo properties
    public $logo;
    public $currentLogoPath;
    
    // Configuración IA (Sólo lectura para el admin de tienda)
    public $hasAiEnabled = false;
    public $ai_provider = '';
    public $ai_model = '';

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ];
    }

    public function mount()
    {
        if (!Auth::check() || !Auth::user()->tenant_id || !Auth::user()->hasRole('admin')) {
            abort(403, 'Acceso denegado: Solo el administrador tiene acceso a esta sección.');
        }

        $tenant = Tenant::findOrFail(Auth::user()->tenant_id);
        
        $this->name = $tenant->name;
        $this->description = $tenant->description;
        $this->currentLogoPath = $tenant->logo_path;
        
        $this->hasAiEnabled = !empty($tenant->ai_api_key);
        $this->ai_provider = $tenant->ai_provider ?? '';
        $this->ai_model = $tenant->ai_model ?? '';
    }

    public function saveSettings()
    {
        $this->validate();

        $tenant = Tenant::findOrFail(Auth::user()->tenant_id);
        
        $data = [
            'name' => $this->name,
            'description' => $this->description,
        ];

        if ($this->logo) {
            $logoPath = $this->logo->store('logos', 'public');
            $data['logo_path'] = $logoPath;
            $this->currentLogoPath = $logoPath;
            $this->reset('logo');
        }

        $tenant->update($data);

        session()->flash('message', 'Perfil y configuración actualizados exitosamente.');
    }

    public function render()
    {
        return view('livewire.vendedor.settings')->layout('components.layouts.app');
    }
}
