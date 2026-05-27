<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Tenant;

class Kiosko extends Component
{
    public $search = '';

    public function render()
    {
        $tenants = Tenant::where('status', 'active')
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->get();

        return view('livewire.kiosko', [
            'tenants' => $tenants
        ])->layout('layouts.app'); // Load default responsive bootstrap layout
    }
}
