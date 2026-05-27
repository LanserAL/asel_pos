<?php

namespace App\Livewire\Admin;

use App\Models\Customer;
use App\Models\CustomerCreditTransaction;
use App\Models\LoyaltyTransaction;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $tab = 'customers';

    protected $queryString = [
        'search' => ['except' => ''],
        'tab' => ['except' => 'customers'],
    ];

    // Customer Form properties (Admin only)
    public $customerId = null;
    public $name = '';
    public $email = '';
    public $phone = '';
    public $rfc = '';
    public $razon_social = '';
    public $regimen_fiscal = '';
    public $postal_code = '';
    public $credit_limit = 0;

    // Payment Form properties (Admin & Vendedor)
    public $paymentCustomerId = null;
    public $paymentAmount = 0;
    public $paymentNotes = '';

    // Detail properties
    public $detailCustomerId = null;
    public $detailCustomer = null;
    public $creditTransactions = [];
    public $loyaltyTransactions = [];

    // Active state for tabs in modal
    public $activeModalTab = 'credit';

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:20',
        'rfc' => 'nullable|string|max:13|min:12',
        'razon_social' => 'nullable|string|max:255',
        'regimen_fiscal' => 'nullable|string|max:255',
        'postal_code' => 'nullable|string|max:10',
        'credit_limit' => 'required|numeric|min:0',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetForm()
    {
        $this->reset(['customerId', 'name', 'email', 'phone', 'rfc', 'razon_social', 'regimen_fiscal', 'postal_code', 'credit_limit']);
        $this->resetValidation();
    }

    public function saveCustomer()
    {
        // Enforce Admin only
        if (!Auth::user()->hasRole('admin')) {
            session()->flash('error', 'Acceso denegado: Solo administradores pueden crear o editar clientes.');
            return;
        }

        $this->validate();

        $tenantId = Auth::user()->tenant_id;

        Customer::updateOrCreate(
            ['id' => $this->customerId, 'tenant_id' => $tenantId],
            [
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'rfc' => strtoupper($this->rfc),
                'razon_social' => $this->razon_social,
                'regimen_fiscal' => $this->regimen_fiscal,
                'postal_code' => $this->postal_code,
                'credit_limit' => $this->credit_limit,
            ]
        );

        session()->flash('message', $this->customerId ? 'Cliente actualizado exitosamente.' : 'Cliente creado exitosamente.');
        $this->resetForm();
        $this->dispatch('close-modal');
    }

    public function editCustomer($id)
    {
        if (!Auth::user()->hasRole('admin')) {
            session()->flash('error', 'Acceso denegado: Solo administradores pueden editar clientes.');
            return;
        }

        $customer = Customer::findOrFail($id);
        $this->customerId = $customer->id;
        $this->name = $customer->name;
        $this->email = $customer->email;
        $this->phone = $customer->phone;
        $this->rfc = $customer->rfc;
        $this->razon_social = $customer->razon_social;
        $this->regimen_fiscal = $customer->regimen_fiscal;
        $this->postal_code = $customer->postal_code;
        $this->credit_limit = $customer->credit_limit;
    }

    public function openPaymentModal($customerId)
    {
        $this->paymentCustomerId = $customerId;
        $this->reset(['paymentAmount', 'paymentNotes']);
        $this->resetValidation();
    }

    public function savePayment()
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
            'paymentNotes' => 'nullable|string|max:255',
        ], [
            'paymentAmount.min' => 'El abono debe ser mayor a $0.00'
        ]);

        try {
            DB::transaction(function () {
                $customer = Customer::findOrFail($this->paymentCustomerId);
                $tenantId = Auth::user()->tenant_id;

                // Create Credit Transaction
                CustomerCreditTransaction::create([
                    'tenant_id' => $tenantId,
                    'customer_id' => $customer->id,
                    'order_id' => null,
                    'type' => 'payment',
                    'amount' => $this->paymentAmount,
                    'notes' => $this->paymentNotes ?: 'Abono a saldo deudor registrado en caja',
                    'processed_by' => Auth::id(),
                ]);

                // Reduce debt balance
                $customer->credit_balance = max(0, $customer->credit_balance - $this->paymentAmount);
                $customer->save();
            });

            session()->flash('message', '¡Abono registrado con éxito! Saldo actualizado en tiempo real.');
            $this->dispatch('close-modal');
            $this->reset(['paymentCustomerId', 'paymentAmount', 'paymentNotes']);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al registrar abono: ' . $e->getMessage());
        }
    }

    public function viewDetails($customerId)
    {
        $this->detailCustomerId = $customerId;
        $this->detailCustomer = Customer::findOrFail($customerId);
        $this->activeModalTab = 'credit';
        $this->loadLedgerData();
    }

    public function setModalTab($tab)
    {
        $this->activeModalTab = $tab;
        $this->loadLedgerData();
    }

    public function loadLedgerData()
    {
        if ($this->detailCustomerId) {
            $this->creditTransactions = CustomerCreditTransaction::where('customer_id', $this->detailCustomerId)
                ->with('processedBy')
                ->latest()
                ->get();

            $this->loyaltyTransactions = LoyaltyTransaction::where('customer_id', $this->detailCustomerId)
                ->latest()
                ->get();
        }
    }

    public function deleteCustomer($id)
    {
        if (!Auth::user()->hasRole('admin')) {
            session()->flash('error', 'Acceso denegado: Solo administradores pueden eliminar clientes.');
            return;
        }

        $customer = Customer::findOrFail($id);
        $customer->delete();
        session()->flash('message', 'Cliente eliminado de forma permanente.');
    }

    public function setTab($tab)
    {
        $this->tab = $tab;
        $this->search = '';
        $this->resetPage();
    }

    public function downloadXml($invoiceId)
    {
        $invoice = Invoice::with(['order', 'customer'])->findOrFail($invoiceId);
        
        $xmlContent = $this->generateSimulatedCfdiXml($invoice);

        return response()->streamDownload(function () use ($xmlContent) {
            echo $xmlContent;
        }, 'FACTURA_' . $invoice->series . $invoice->folio . '.xml', [
            'Content-Type' => 'application/xml',
        ]);
    }

    public function generateSimulatedCfdiXml($invoice)
    {
        $rfc = $invoice->customer->rfc ?: 'XAXX010101000';
        $razonSocial = htmlspecialchars($invoice->customer->razon_social ?: 'PUBLICO EN GENERAL');
        $regimen = $invoice->customer->regimen_fiscal ?: '605';
        $cp = $invoice->customer->postal_code ?: '01000';
        
        $subtotal = number_format($invoice->order->subtotal, 2, '.', '');
        $tax = number_format($invoice->order->tax, 2, '.', '');
        $total = number_format($invoice->order->total, 2, '.', '');
        $date = $invoice->created_at->format('Y-m-d\TH:i:s');
        $uuid = $invoice->uuid;

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd" Version="4.0" Serie="{$invoice->series}" Folio="{$invoice->folio}" Fecha="{$date}" Sello="MIIEpQIBAAKCAQEA0T1yQ..." NoCertificado="00001000000504465028" SubTotal="{$subtotal}" Total="{$total}" Moneda="MXN" TipoDeComprobante="I" Exportacion="01" MetodoPago="PUE" LugarExpedicion="01000">
    <cfdi:Emisor Rfc="ASE001010XX1" Nombre="ASEL POS GROUP SOFTWARE" RegimenFiscal="601"/>
    <cfdi:Receptor Rfc="{$rfc}" Nombre="{$razonSocial}" DomicilioFiscalReceptor="{$cp}" RegimenFiscalReceptor="{$regimen}" UsoCFDI="G03"/>
    <cfdi:Conceptos>
        <cfdi:Concepto ClaveProdServ="50161800" Cantidad="1.0" ClaveUnidad="H87" Descripcion="Consumo de Punto de Venta - Ticket #{$invoice->order_id}" ValorUnitario="{$subtotal}" Importe="{$subtotal}">
            <cfdi:Impuestos>
                <cfdi:Traslados>
                    <cfdi:Traslado Base="{$subtotal}" Impuesto="002" TipoFactor="Tasa" TasaOCuota="0.160000" Importe="{$tax}"/>
                </cfdi:Traslados>
            </cfdi:Impuestos>
        </cfdi:Concepto>
    </cfdi:Conceptos>
    <cfdi:Impuestos TotalImpuestosTrasladados="{$tax}">
        <cfdi:Traslados>
            <cfdi:Traslado Base="{$subtotal}" Impuesto="002" TipoFactor="Tasa" TasaOCuota="0.160000" Importe="{$tax}"/>
        </cfdi:Traslados>
    </cfdi:Impuestos>
    <cfdi:Complemento>
        <tfd:TimbreFiscalDigital xmlns:tfd="http://www.sat.gob.mx/TimbreFiscalDigital" xsi:schemaLocation="http://www.sat.gob.mx/TimbreFiscalDigital http://www.sat.gob.mx/sitio_internet/cfd/TimbreFiscalDigital/TimbreFiscalDigitalv11.xsd" Version="1.1" UUID="{$uuid}" FechaTimbrado="{$date}" RfcProvCertif="SAT970701NN3" SelloCFD="aB5k9..." SelloSAT="xP93lks..."/>
    </cfdi:Complemento>
</cfdi:Comprobante>
XML;
    }

    public function downloadPdf($invoiceId)
    {
        $invoice = Invoice::with(['order', 'customer'])->findOrFail($invoiceId);
        
        $htmlContent = $this->generateSimulatedInvoiceHtml($invoice);

        return response()->streamDownload(function () use ($htmlContent) {
            echo $htmlContent;
        }, 'FACTURA_' . $invoice->series . $invoice->folio . '.html', [
            'Content-Type' => 'text/html',
        ]);
    }

    public function generateSimulatedInvoiceHtml($invoice)
    {
        $rfc = $invoice->customer->rfc ?: 'XAXX010101000';
        $razonSocial = htmlspecialchars($invoice->customer->razon_social ?: 'PUBLICO EN GENERAL');
        $regimen = $invoice->customer->regimen_fiscal ?: '605';
        $cp = $invoice->customer->postal_code ?: '01000';
        
        $subtotal = number_format($invoice->order->subtotal, 2);
        $tax = number_format($invoice->order->tax, 2);
        $total = number_format($invoice->order->total, 2);
        $date = $invoice->created_at->format('d/m/Y H:i:s');
        $uuid = $invoice->uuid;

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura Electrónica CFDI 4.0</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.5; padding: 30px; }
        .header { width: 100%; border-bottom: 2px solid #1a2b4c; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; color: #1a2b4c; }
        .meta { float: right; text-align: right; }
        .section { margin-bottom: 20px; }
        .section-title { font-size: 12px; font-weight: bold; background: #f2f2f2; padding: 5px; margin-bottom: 10px; color: #1a2b4c; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th { background: #1a2b4c; color: white; padding: 8px; text-align: left; }
        .table td { padding: 8px; border-bottom: 1px solid #ddd; }
        .text-right { text-align: right; }
        .footer { font-size: 9px; color: #888; border-top: 1px solid #ddd; padding-top: 10px; margin-top: 40px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="meta">
            <strong>Factura Serie {$invoice->series} Folio {$invoice->folio}</strong><br>
            Fecha: {$date}<br>
            <strong>UUID Fiscal: {$uuid}</strong>
        </div>
        <div class="title">ASEL POS GROUP SOFTWARE</div>
        <div>Rfc: ASE001010XX1 | Régimen Fiscal: 601 - General de Ley Personas Morales</div>
    </div>

    <div class="section">
        <div class="section-title">Datos del Receptor</div>
        <table style="width: 100%; font-size: 11px;">
            <tr>
                <td style="width: 15%;"><strong>Cliente:</strong></td>
                <td>{$invoice->customer->name}</td>
                <td style="width: 15%;"><strong>RFC:</strong></td>
                <td>{$rfc}</td>
            </tr>
            <tr>
                <td><strong>Nombre Fiscal:</strong></td>
                <td>{$razonSocial}</td>
                <td><strong>Régimen Fiscal:</strong></td>
                <td>{$regimen}</td>
            </tr>
            <tr>
                <td><strong>Código Postal:</strong></td>
                <td colspan="3">{$cp}</td>
            </tr>
        </table>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Clave ProdServ</th>
                <th>Descripción</th>
                <th class="text-right">Cantidad</th>
                <th class="text-right">Precio Unitario</th>
                <th class="text-right">Importe</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>50161800</td>
                <td>Consumo de Punto de Venta - Ticket #{$invoice->order_id}</td>
                <td class="text-right">1.0</td>
                <td class="text-right">\${$subtotal}</td>
                <td class="text-right">\${$subtotal}</td>
            </tr>
        </tbody>
    </table>

    <table style="width: 40%; float: right; font-size: 12px; margin-top: 20px;">
        <tr>
            <td><strong>Subtotal:</strong></td>
            <td class="text-right">\${$subtotal}</td>
        </tr>
        <tr>
            <td><strong>IVA Traslado (16%):</strong></td>
            <td class="text-right">\${$tax}</td>
        </tr>
        <tr style="font-size: 14px; color: #ff6b6b; font-weight: bold;">
            <td><strong>Total:</strong></td>
            <td class="text-right">\${$total}</td>
        </tr>
    </table>
    <div style="clear: both;"></div>

    <div class="footer">
        Este documento es una representación impresa y simplificada de un CFDI 4.0 emitido por ASEL POS.<br>
        Sello Digital Emisor: aB5k9jHkS910skAs...<br>
        Sello SAT: xP93lksD91skSlskD1s...
    </div>
</body>
</html>
HTML;
    }

    public function cancelInvoice($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $invoice->status = 'cancelled';
        $invoice->save();

        session()->flash('message', '¡Factura cancelada con éxito! Se ha revocado el timbre fiscal CFDI.');
    }

    public function render()
    {
        if ($this->tab === 'invoices') {
            $invoices = Invoice::join('customers', 'invoices.customer_id', '=', 'customers.id')
                ->select('invoices.*', 'customers.name as customer_name')
                ->where(function ($query) {
                    $query->where('invoices.folio', 'like', '%' . $this->search . '%')
                          ->orWhere('invoices.uuid', 'like', '%' . $this->search . '%')
                          ->orWhere('customers.name', 'like', '%' . $this->search . '%');
                })
                ->latest('invoices.created_at')
                ->paginate(10);

            return view('livewire.admin.customer-management', [
                'invoices' => $invoices
            ])->layout('components.layouts.app');
        } else {
            $customers = Customer::where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('rfc', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

            return view('livewire.admin.customer-management', [
                'customers' => $customers
            ])->layout('components.layouts.app');
        }
    }
}
