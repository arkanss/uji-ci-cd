<?php

namespace App\Livewire\Finance\PurchaseOrder;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Title;
use Carbon\Carbon;

#[Title('Purchase Order Form')]
class PurchaseOrderForm extends Component
{
    public $poId = null;

    // Header Section
    public $po_number, $date, $expected_delivery_date, $warehouse_id, $status;
    public $vendor_id, $vendor_code, $vendor_address, $vendor_contact_phone, $vendor_contact_email;
    public $ship_to_address, $currency = 'IDR', $payment_terms, $delivery_terms;

    // Totals Section
    public $subtotal = 0, $total_discount = 0, $tax_total = 0;
    public $freight_charges = null, $other_charges = null, $grand_total = 0;

    // Additional Information
    public $internal_notes, $vendor_notes, $reference_number;
    public $approved_by, $approval_date;

    public $warehouse_address, $warehouse_phone, $warehouse_email;

    // Line Items
    public $items = [];

    public function mount($id = null)
    {
        $this->poId = $id;

        if ($id) {
            $this->loadPurchaseOrder($id);
        } else {
            $this->date = now()->format('Y-m-d');
            $this->expected_delivery_date = now()->addDays(7)->format('Y-m-d');
        }
    }

    public function loadPurchaseOrder($id)
    {
        $po = PurchaseOrder::findOrFail($id);

        $this->po_number = $po->po_number;
        $this->date = $po->date->format('Y-m-d');
        $this->expected_delivery_date = $po->expected_delivery_date ? $po->expected_delivery_date->format('Y-m-d') : '';
        $this->warehouse_id = $po->warehouse_id;
        $this->status = $po->status;
        $this->vendor_id = $po->vendor_id;
        $this->vendor_code = $po->vendor_code;
        $this->vendor_address = $po->vendor_address;
        $this->vendor_contact_phone = $po->vendor_contact_phone;
        $this->vendor_contact_email = $po->vendor_contact_email;
        $this->ship_to_address = $po->ship_to_address;
        $this->currency = $po->currency ?? 'IDR';
        $this->payment_terms = $po->payment_terms;
        $this->delivery_terms = $po->delivery_terms;
        $this->subtotal = $po->subtotal;
        $this->total_discount = $po->total_discount;
        $this->tax_total = $po->tax_total;
        $this->freight_charges = $po->freight_charges;
        $this->other_charges = $po->other_charges;
        $this->grand_total = $po->grand_total;
        $this->internal_notes = $po->internal_notes;
        $this->vendor_notes = $po->vendor_notes;
        $this->reference_number = $po->reference_number;
        $this->vendor_id = $po->vendor_id;
        $this->warehouse_id = $po->warehouse_id;
        $this->calculateTotals();

        if ($this->vendor_id) {
            $vendor = Vendor::find($this->vendor_id);
            if ($vendor) {
                $this->vendor_code = $po->vendor_code;
                $this->vendor_address = $po->vendor_address; 
                $this->vendor_contact_phone = $po->vendor_contact_phone;
                $this->vendor_contact_email = $po->vendor_contact_email;
                $this->currency = $po->currency;
            }
        }

        if ($this->warehouse_id) {
            $warehouse = DB::table('warehouse_addresses')
                ->where('id', $this->warehouse_id)
                ->first();

            if ($warehouse) {
                $this->warehouse_address = $warehouse->address;
                $this->warehouse_phone   = $warehouse->phone_number;
                $this->warehouse_email   = $warehouse->email ?? null;
            }
        }

        $this->items = PurchaseOrderItem::where('purchase_order_id', $po->id)
            ->get()
            ->map(fn($it) => [
                'product_id' => $it->product_id,
                'item_description' => $it->item_description,
                'uom' => $it->uom,
                'requested_stock' => $it->requested_stock,
                'unit_id' => $it->unit_id,
                'unit_price' => $it->unit_price,
                'discount_percent' => $it->discount_percent,
                'discount_amount' => $it->discount_amount,
                'tax_code' => $it->tax_code,
                'tax_amount' => $it->tax_amount,
                'line_total' => $it->line_total,
                'requested_delivery_date' => $it->requested_delivery_date ? $it->requested_delivery_date->format('Y-m-d') : '',
                'warehouse_bin_location' => $it->warehouse_bin_location,
            ])->toArray();
    }

    public function addItem()
    {
        $this->items[] = [
            'product_id' => null,
            'item_description' => '',
            'uom' => '',
            'requested_stock' => null,
            'unit_id' => null,
            'unit_price' => 0,
            'discount_percent' => null,
            'discount_amount' => 0,
            'tax_code' => '',
            'tax_amount' => null,
            'line_total' => 0,
            'requested_delivery_date' => '',
            'warehouse_bin_location' => '',
        ];
    }

    public function removeItem($index)
    {
        if (isset($this->items[$index])) {
            array_splice($this->items, $index, 1);
            $this->calculateTotals();
        }
    }

    public function updatedVendorId($value)
    {
        if (!$value) return;

        $vendor = Vendor::find($value);
        if (!$vendor) return;

        $this->vendor_code = $vendor->vendor_code;
        $this->vendor_address = $vendor->billing_address;

        if (!$this->poId) {
            $this->vendor_contact_phone = $vendor->phone;
            $this->vendor_contact_email = $vendor->email;
            $this->payment_terms = $vendor->payment_terms;
            $this->currency = $vendor->currency ?? 'IDR';
            $this->delivery_terms = $vendor->delivery_terms;
        }
    }


    public function updatedWarehouseId($value)
    {
        if (!$value) {
            $this->warehouse_address = null;
            $this->warehouse_phone = null;
            $this->warehouse_email = null;
            return;
        }

        $warehouse = DB::table('warehouse_addresses')
            ->where('id', $value)
            ->first();

        if ($warehouse) {
            $this->warehouse_address = $warehouse->address;
            $this->warehouse_phone   = $warehouse->phone_number;
            $this->warehouse_email   = $warehouse->email ?? null;
        }
    }

    public function calculateLineTotal($index)
    {
        if (!isset($this->items[$index])) {
            return;
        }

        $item = &$this->items[$index];
        $quantity = (float) ($item['requested_stock'] ?? 0);
        $unitPrice = (float) ($item['unit_price'] ?? 0);
        $discountPercent = (float) ($item['discount_percent'] ?? 0);

        $subtotal = $quantity * $unitPrice;
        $discountAmount = ($subtotal * $discountPercent) / 100;
        $item['discount_amount'] = round($discountAmount, 2);

        $taxAmount = (float) ($item['tax_amount'] ?? 0);
        $item['line_total'] = round($subtotal - $discountAmount + $taxAmount, 2);

        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->subtotal = 0;
        $this->total_discount = 0;
        $this->tax_total = 0;

        foreach ($this->items as &$item) {
            $qty = (float) ($item['requested_stock'] ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);
            $discountPercent = (float) ($item['discount_percent'] ?? 0);

            $lineSubtotal = $qty * $price;
            $discountAmount = ($lineSubtotal * $discountPercent) / 100;

            $item['discount_amount'] = round($discountAmount, 2);

            $this->subtotal += $lineSubtotal;
            $this->total_discount += $item['discount_amount'];
            $this->tax_total += (float) ($item['tax_amount'] ?? 0);
        }

        $this->grand_total =
            $this->subtotal
            - $this->total_discount
            + $this->tax_total
            + (float) ($this->freight_charges ?? 0)
            + (float) ($this->other_charges ?? 0);

        $this->grand_total = round($this->grand_total, 2);
    }


    public function updated($propertyName)
    {
        if (
            str_starts_with($propertyName, 'items.') &&
            (str_contains($propertyName, '.requested_stock') ||
                str_contains($propertyName, '.unit_price') ||
                str_contains($propertyName, '.discount_percent') ||
                str_contains($propertyName, '.tax_amount'))
        ) {

            preg_match('/items\.(\d+)/', $propertyName, $matches);
            if (isset($matches[1])) {
                $this->calculateLineTotal((int) $matches[1]);
            }
        }

        if (in_array($propertyName, ['freight_charges', 'other_charges'])) {
            $this->calculateTotals();
        }
    }

    public function save()
    {
        $this->validate([
            'date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'vendor_id' => 'required',
            'warehouse_id' => 'required',
            'currency' => 'required',
            'items' => 'array',
        ]);

        $validItems = array_values(array_filter($this->items, fn($it) => !empty($it['product_id'])));
        if (count($validItems) === 0) {
            session()->flash('po_error', 'Please add at least one item before saving.');
            return;
        }

        $this->validate([
            'items.*.product_id' => 'required',
            'items.*.requested_stock' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validItems) {
            $poData = [
                'date' => Carbon::parse($this->date),
                'expected_delivery_date' => $this->expected_delivery_date ? Carbon::parse($this->expected_delivery_date) : null,
                'warehouse_id' => $this->warehouse_id,
                'vendor_id' => $this->vendor_id,
                'vendor_code' => $this->vendor_code,
                'vendor_address' => $this->vendor_address,
                'ship_to_address' => $this->ship_to_address,
                'currency' => $this->currency,
                'payment_terms' => $this->payment_terms,
                'delivery_terms' => $this->delivery_terms,
                'subtotal' => $this->subtotal,
                'total_discount' => $this->total_discount,
                'tax_total' => $this->tax_total,
                'freight_charges' => $this->freight_charges ?? 0,
                'other_charges' => $this->other_charges ?? 0,
                'grand_total' => $this->grand_total,
                'internal_notes' => $this->internal_notes,
                'vendor_notes' => $this->vendor_notes,
                'reference_number' => $this->reference_number,
                'status' => $this->status ?? 1,
                'created_by' => Auth::id(),
            ];

            if (empty($this->poId)) {
                $datePart = Carbon::parse($this->date)->format('Ymd');
                $last = PurchaseOrder::whereDate('created_at', Carbon::parse($this->date)->toDateString())
                    ->where('po_number', 'like', "PO-{$datePart}-%")
                    ->orderBy('po_number', 'desc')
                    ->value('po_number');

                $seq = 1;
                if ($last) {
                    $lastSeq = (int) substr($last, -3);
                    $seq = $lastSeq + 1;
                }
                $poNumber = 'PO-' . $datePart . '-' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
                $poData['po_number'] = $poNumber;
                $this->po_number = $poNumber;
            }

            $po = PurchaseOrder::updateOrCreate(['id' => $this->poId], $poData);

            PurchaseOrderItem::where('purchase_order_id', $po->id)->delete();

            foreach ($validItems as $it) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $it['product_id'],
                    'item_description' => $it['item_description'] ?? '',
                    'uom' => $it['uom'] ?? '',
                    'requested_stock' => $it['requested_stock'],
                    'unit_id' => $it['unit_id'] ?? null,
                    'unit_price' => $it['unit_price'] ?? 0,
                    'discount_percent' => $it['discount_percent'] ?? 0,
                    'discount_amount' => $it['discount_amount'] ?? 0,
                    'tax_code' => $it['tax_code'] ?? '',
                    'tax_amount' => $it['tax_amount'] ?? 0,
                    'line_total' => $it['line_total'] ?? 0,
                    'requested_delivery_date' => !empty($it['requested_delivery_date']) ? Carbon::parse($it['requested_delivery_date']) : null,
                    'warehouse_bin_location' => $it['warehouse_bin_location'] ?? '',
                    'received_stock' => null,
                ]);
            }
        });

        session()->flash('po_success', 'Purchase Order saved successfully');
        return redirect()->route('finance.purchase-order.index');
    }

    public function cancel()
    {
        return redirect()->route('finance.purchase-order.index');
    }

    public function render()
    {
        $warehouses = DB::table('warehouse_addresses')->select('id', 'name')->get();
        $products = Product::select('id', 'name')->orderBy('name')->get();
        $units = Unit::select('id', 'name')->orderBy('name')->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();

        return view('livewire.finance.purchase-order.purchase-order-form', [
            'warehouses' => $warehouses,
            'products' => $products,
            'units' => $units,
            'vendors' => $vendors,
        ])->layout('layouts.app', [
            'breadcrumbs' => breadcrumbs(
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Finance', 'url' => '#'],
                ['label' => 'Purchase Orders', 'url' => route('finance.purchase-order.index')],
                $this->poId ? 'Edit' : 'Create'
            ),
        ]);
    }
}
