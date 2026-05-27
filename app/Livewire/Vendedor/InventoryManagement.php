<?php

namespace App\Livewire\Vendedor;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $branchIdFilter = '';
    
    public $branch_id, $product_id, $stock_quantity, $alert_min_stock;
    public $inventoryId = null;

    protected $rules = [
        'branch_id' => 'required|exists:branches,id',
        'product_id' => 'required|exists:products,id',
        'stock_quantity' => 'required|integer|min:0',
        'alert_min_stock' => 'required|integer|min:0',
    ];

    public function mount()
    {
        if (!Auth::check() || !Auth::user()->tenant_id) {
            abort(403, 'Acceso denegado.');
        }

        if (Auth::user()->hasRole('vendedor') && Auth::user()->branch_id) {
            $this->branchIdFilter = Auth::user()->branch_id;
        }
    }

    public function saveInventory()
    {
        $this->validate();

        // Check if inventory already exists for this branch/product combo
        $existing = Inventory::where('branch_id', $this->branch_id)
            ->where('product_id', $this->product_id)
            ->first();

        if ($existing && !$this->inventoryId) {
            session()->flash('error', 'Este producto ya tiene un registro de inventario en esta sucursal. Edita el existente en lugar de crear uno nuevo.');
            return;
        }

        Inventory::updateOrCreate(
            ['id' => $this->inventoryId],
            [
                'branch_id' => $this->branch_id,
                'product_id' => $this->product_id,
                'stock_quantity' => $this->stock_quantity,
                'alert_min_stock' => $this->alert_min_stock,
            ]
        );

        session()->flash('message', $this->inventoryId ? 'Inventario actualizado exitosamente.' : 'Inventario registrado exitosamente.');
        
        $this->resetForm();
        $this->dispatch('close-modal');
    }

    public function editInventory($id)
    {
        $inventory = Inventory::findOrFail($id);
        $this->inventoryId = $inventory->id;
        $this->branch_id = $inventory->branch_id;
        $this->product_id = $inventory->product_id;
        $this->stock_quantity = $inventory->stock_quantity;
        $this->alert_min_stock = $inventory->alert_min_stock;
    }

    public function resetForm()
    {
        $this->reset(['branch_id', 'product_id', 'stock_quantity', 'alert_min_stock', 'inventoryId']);
        $this->resetValidation();
    }

    public function render()
    {
        $user = Auth::user();
        
        if ($user->hasRole('vendedor') && $user->branch_id) {
            $branches = Branch::where('id', $user->branch_id)->get();
            $this->branchIdFilter = $user->branch_id;
        } else {
            $branches = Branch::all();
        }
        
        $products = Product::all();

        $query = Inventory::with(['product', 'branch'])
            ->whereHas('product', function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%');
            });

        if ($this->branchIdFilter) {
            $query->where('branch_id', $this->branchIdFilter);
        }

        $inventories = $query->latest()->paginate(10);

        return view('livewire.vendedor.inventory-management', [
            'inventories' => $inventories,
            'branches' => $branches,
            'products' => $products,
        ])->layout('components.layouts.app');
    }
}
