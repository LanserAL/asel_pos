<?php

namespace App\Livewire\Vendedor;

use App\Models\Promotion;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PromotionManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $name, $type = 'code', $code, $discount_type = 'percentage', $discount_value = 0, $min_quantity = 1, $start_date, $end_date, $status = 'active';
    
    // Selection state for products inside the promotion
    public $selectedProducts = []; // List of selected product IDs
    public $comboQuantities = [];   // Mapping of product_id => required quantity
    
    public $promotionId = null;
    public $showCreateModal = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'type' => 'required|in:code,quantity,combo',
        'code' => 'nullable|string|max:255',
        'discount_type' => 'required|in:percentage,fixed_discount,fixed_price',
        'discount_value' => 'required|numeric|min:0.01',
        'min_quantity' => 'nullable|integer|min:1',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'status' => 'required|in:active,inactive',
    ];

    public function mount()
    {
        if (!Auth::check() || !Auth::user()->tenant_id) {
            abort(403, 'Acceso denegado.');
        }

        // Restrict this view to Admin role only
        if (!Auth::user()->hasRole('admin')) {
            abort(403, 'Acceso restringido únicamente a administradores.');
        }
    }

    public function render()
    {
        $tenantId = Auth::user()->tenant_id;

        // Fetch promotions with products
        $promotions = Promotion::where('tenant_id', $tenantId)
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%');
            })
            ->with('products')
            ->latest()
            ->paginate(10);

        // Fetch all active products to let the admin select them in the dropdown
        $allProducts = Product::where('status', 'active')->orderBy('title', 'asc')->get();

        return view('livewire.vendedor.promotion-management', [
            'promotions' => $promotions,
            'allProducts' => $allProducts
        ]);
    }

    public function addProductToPromotion($productId)
    {
        $productId = (int)$productId;
        if (!in_array($productId, $this->selectedProducts)) {
            $this->selectedProducts[] = $productId;
            $this->comboQuantities[$productId] = 1; // Default required qty to 1
        }
    }

    public function removeProductFromPromotion($productId)
    {
        $productId = (int)$productId;
        $this->selectedProducts = array_filter($this->selectedProducts, function ($id) use ($productId) {
            return $id !== $productId;
        });
        unset($this->comboQuantities[$productId]);
    }

    public function resetFields()
    {
        $this->reset([
            'promotionId', 'name', 'type', 'code', 'discount_type', 
            'discount_value', 'min_quantity', 'start_date', 'end_date', 
            'status', 'selectedProducts', 'comboQuantities'
        ]);
        $this->type = 'code';
        $this->discount_type = 'percentage';
        $this->status = 'active';
        $this->min_quantity = 1;
        $this->discount_value = 0;
    }

    public function openNewPromotionModal()
    {
        $this->resetFields();
        $this->showCreateModal = true;
    }

    public function savePromotion()
    {
        // Custom validations based on promotion type
        if ($this->type === 'code') {
            $this->rules['code'] = 'required|string|max:50';
            $this->min_quantity = null;
        } else {
            $this->code = null;
        }

        if ($this->type === 'quantity') {
            $this->rules['min_quantity'] = 'required|integer|min:2';
        }

        $this->validate();

        // Extra validation: make sure products are selected for volume and combo promotions
        if ($this->type !== 'code' && empty($this->selectedProducts)) {
            $this->addError('selectedProducts', 'Debes seleccionar al menos un producto para esta promoción.');
            return;
        }

        $tenantId = Auth::user()->tenant_id;

        // Verify promo code uniqueness for code type
        if ($this->type === 'code') {
            $existing = Promotion::where('tenant_id', $tenantId)
                ->where('type', 'code')
                ->where('code', strtoupper($this->code))
                ->when($this->promotionId, function ($query) {
                    $query->where('id', '!=', $this->promotionId);
                })
                ->first();

            if ($existing) {
                $this->addError('code', 'Este código de cupón ya está en uso en otra promoción.');
                return;
            }
        }

        $promotion = Promotion::updateOrCreate(
            ['id' => $this->promotionId, 'tenant_id' => $tenantId],
            [
                'name' => $this->name,
                'type' => $this->type,
                'code' => $this->code ? strtoupper($this->code) : null,
                'discount_type' => $this->discount_type,
                'discount_value' => $this->discount_value,
                'min_quantity' => $this->min_quantity,
                'start_date' => $this->start_date ? \Carbon\Carbon::parse($this->start_date) : null,
                'end_date' => $this->end_date ? \Carbon\Carbon::parse($this->end_date) : null,
                'status' => $this->status,
            ]
        );

        // Sync products with quantities
        $syncData = [];
        if ($this->type !== 'code') {
            foreach ($this->selectedProducts as $productId) {
                $qty = isset($this->comboQuantities[$productId]) ? max(1, (int)$this->comboQuantities[$productId]) : 1;
                $syncData[$productId] = ['quantity' => $qty];
            }
        }
        $promotion->products()->sync($syncData);

        session()->flash('success', $this->promotionId ? 'Promoción actualizada con éxito.' : 'Nueva promoción registrada con éxito.');
        $this->showCreateModal = false;
        $this->resetFields();
    }

    public function editPromotion($id)
    {
        $this->resetFields();
        $promotion = Promotion::where('tenant_id', Auth::user()->tenant_id)->findOrFail($id);

        $this->promotionId = $promotion->id;
        $this->name = $promotion->name;
        $this->type = $promotion->type;
        $this->code = $promotion->code;
        $this->discount_type = $promotion->discount_type;
        $this->discount_value = $promotion->discount_value;
        $this->min_quantity = $promotion->min_quantity;
        $this->start_date = $promotion->start_date ? $promotion->start_date->format('Y-m-d\TH:i') : null;
        $this->end_date = $promotion->end_date ? $promotion->end_date->format('Y-m-d\TH:i') : null;
        $this->status = $promotion->status;

        // Load relations
        foreach ($promotion->products as $prod) {
            $this->selectedProducts[] = $prod->id;
            $this->comboQuantities[$prod->id] = $prod->pivot->quantity;
        }

        $this->showCreateModal = true;
    }

    public function toggleStatus($id)
    {
        $promotion = Promotion::where('tenant_id', Auth::user()->tenant_id)->findOrFail($id);
        $promotion->status = $promotion->status === 'active' ? 'inactive' : 'active';
        $promotion->save();

        session()->flash('success', 'El estado de la promoción fue actualizado correctamente.');
    }

    public function deletePromotion($id)
    {
        $promotion = Promotion::where('tenant_id', Auth::user()->tenant_id)->findOrFail($id);
        $promotion->delete();

        session()->flash('success', 'La promoción fue eliminada permanentemente del sistema.');
    }
}
