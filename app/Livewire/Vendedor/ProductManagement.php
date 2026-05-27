<?php

namespace App\Livewire\Vendedor;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class ProductManagement extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $search = '';
    public $title, $sku, $barcode, $description, $raw_title, $price, $cost, $status = 'active';
    public $image;
    public $productId = null;
    public $pairingToken;

    // AI Generation state
    public $isGenerating = false;
    public $aiPrompt = '';

    protected $rules = [
        'title' => 'required|string|max:255',
        'sku' => 'nullable|string|max:255',
        'barcode' => 'nullable|string|max:255',
        'description' => 'nullable|string',
        'raw_title' => 'nullable|string|max:255',
        'price' => 'required|numeric|min:0',
        'cost' => 'required|numeric|min:0',
        'status' => 'required|in:active,inactive',
        'image' => 'nullable|image|max:2048', // 2MB Max
    ];

    public function mount()
    {
        if (!Auth::check() || !Auth::user()->tenant_id) {
            abort(403, 'Acceso denegado.');
        }

        // Generar token único para el escáner móvil en el panel de productos
        $this->pairingToken = session()->get('product_pairing_token', 'PRD_' . \Illuminate\Support\Str::random(12));
        session()->put('product_pairing_token', $this->pairingToken);
    }

    public function generateWithAI()
    {
        $this->validate(['aiPrompt' => 'required|string|min:3']);
        $this->isGenerating = true;

        // Simulando llamada a la API (Fase 3 se conectará real)
        $prompt = $this->aiPrompt;
        
        // Simulación de retraso de red
        sleep(2);

        $this->title = ucwords($prompt) . " Premium";
        $this->raw_title = strtolower($prompt);
        $this->description = "Este es un excelente producto generado automáticamente por IA basándose en tu solicitud: '{$prompt}'. Disfruta de la mejor calidad y rendimiento en tu negocio.";
        
        $this->isGenerating = false;
        $this->aiPrompt = '';
        
        session()->flash('ai_message', '✨ ¡Contenido generado con Inteligencia Artificial!');
    }

    public function saveProduct()
    {
        $this->validate();

        $product = $this->productId ? Product::find($this->productId) : null;
        $imagePath = $product ? $product->image_path : null;
        
        if ($this->image) {
            if ($imagePath && \Illuminate\Support\Facades\Storage::disk('public')->exists($imagePath)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $this->image->store('products', 'public');
        }

        Product::updateOrCreate(
            ['id' => $this->productId, 'tenant_id' => Auth::user()->tenant_id],
            [
                'title' => $this->title,
                'sku' => $this->sku,
                'barcode' => $this->barcode,
                'description' => $this->description,
                'raw_title' => $this->raw_title,
                'price' => $this->price,
                'cost' => $this->cost,
                'status' => $this->status,
                'image_path' => $imagePath,
            ]
        );

        session()->flash('message', $this->productId ? 'Producto actualizado exitosamente.' : 'Producto registrado exitosamente.');
        
        $this->resetForm();
        $this->dispatch('close-modal');
    }

    public function editProduct($id)
    {
        $product = Product::findOrFail($id);
        $this->productId = $product->id;
        $this->title = $product->title;
        $this->sku = $product->sku;
        $this->barcode = $product->barcode;
        $this->description = $product->description;
        $this->raw_title = $product->raw_title;
        $this->price = $product->price;
        $this->cost = $product->cost;
        $this->status = $product->status;
        $this->image = null; // No cargamos la imagen en el input file
    }

    public function resetForm()
    {
        $this->reset(['title', 'sku', 'barcode', 'description', 'raw_title', 'price', 'cost', 'status', 'productId', 'image', 'aiPrompt']);
        $this->resetValidation();
    }

    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['status' => $product->status === 'active' ? 'inactive' : 'active']);
        session()->flash('message', 'Estado de producto actualizado.');
    }

    /**
     * Verificar si el celular vinculado ha escaneado algún código
     */
    public function checkMobileScans()
    {
        if (!$this->pairingToken) return;

        $scan = \App\Models\ScannerScan::where('pairing_token', $this->pairingToken)
            ->where('is_processed', false)
            ->first();

        if ($scan) {
            $this->barcode = $scan->barcode;
            $scan->is_processed = true;
            $scan->save();
            
            session()->flash('scanner_message', "¡Código '{$scan->barcode}' escaneado desde tu celular!");
        }
    }

    public function render()
    {
        $products = Product::with(['inventories.branch'])
            ->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.vendedor.product-management', [
            'products' => $products
        ])->layout('components.layouts.app');
    }
}
