<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Models\Scopes\TenantScope;

class OrderTracking extends Component
{
    public $orderId = null;
    public $order = null;
    public $searchTicketId = '';

    public function mount($orderId = null)
    {
        $this->orderId = $orderId;
        if ($this->orderId) {
            $this->loadOrder();
        }
    }

    public function loadOrder()
    {
        if (!$this->orderId) return;

        // Bypass global TenantScope to allow secure public guest access to tracking pages
        $this->order = Order::withoutGlobalScope(TenantScope::class)
            ->with(['tenant', 'branch'])
            ->find($this->orderId);
    }

    public function recoverTicket()
    {
        $this->validate([
            'searchTicketId' => 'required|string|min:1'
        ], [
            'searchTicketId.required' => 'Debes ingresar un número de ticket.'
        ]);

        // Clean ticket prefix like '#' or spaces
        $cleanId = trim(str_replace('#', '', $this->searchTicketId));

        if (!is_numeric($cleanId)) {
            session()->flash('search_error', 'Por favor ingresa un número de ticket válido.');
            return;
        }

        $foundOrder = Order::withoutGlobalScope(TenantScope::class)->find($cleanId);

        if ($foundOrder) {
            $this->orderId = $foundOrder->id;
            $this->loadOrder();
            // Redirect to URL with ID to make it bookmarkable
            return redirect()->route('orders.tracking', ['orderId' => $this->orderId]);
        } else {
            session()->flash('search_error', 'No se encontró ningún pedido con el número de ticket #' . $cleanId);
        }
    }

    public function render()
    {
        // Reload order state on render to pick up changes during wire:poll
        if ($this->orderId) {
            $this->loadOrder();
        }

        return view('livewire.order-tracking', [
            'o' => $this->order
        ])->layout('layouts.app');
    }
}
