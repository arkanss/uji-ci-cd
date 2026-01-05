<div class="p-6 max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <flux:heading size="xl">{{ $poId ? 'Edit Purchase Order' : 'Buat Purchase Order' }}</flux:heading>
        <flux:subheading>
            @if ($po_number)
            Nomor PO: {{ $po_number }}
            @else
            Nomor PO akan secara otomatis dibuat setelah penyimpanan.
            @endif
        </flux:subheading>
    </div>

    {{-- Flash Messages --}}
    @if (session('po_success'))
    <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 rounded">
        <p class="text-green-800 dark:text-green-200">{{ session('po_success') }}</p>
    </div>
    @endif

    @if (session('po_error'))
    <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 rounded">
        <p class="text-red-800 dark:text-red-200">{{ session('po_error') }}</p>
    </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-6">
        <div class="grid grid-cols-3 gap-4">

            <flux:field>
                <flux:label>Currency*</flux:label>

                <x-searchable-select
                    wire:model="currency"
                    :options="[
                        ['value' => 'IDR', 'label' => 'IDR'],
                        ['value' => 'USD', 'label' => 'USD'],
                        ['value' => 'SGD', 'label' => 'SGD'],
                    ]"
                    placeholder="Select Currency..."
                />

                <flux:error name="currency" />
            </flux:field>

            <flux:field>
                <flux:label>Date*</flux:label>
                <flux:input type="date" wire:model="date" />
                <flux:error name="date" />
            </flux:field>

            <flux:field>
                <flux:label>Expected Delivery Date*</flux:label>
                <flux:input type="date" wire:model="expected_delivery_date" />
                <flux:error name="expected_delivery_date" />
            </flux:field>

        </div>

        <div class="grid grid-cols-2 gap-4">
            {{-- Vendor Information --}}
            <x-ui.card title="Vendor Information">
                <div class="grid grid-cols-2 gap-4">
                    <flux:field class="col-span-2">
                        <flux:label>Vendor*</flux:label>
                        <x-searchable-select wire:model.live="vendor_id" name="vendor_id" :value="$vendor_id"
                            :options='$vendors->map(fn($v) => ["value" => $v->id, "label" => $v->name . " (" . $v->vendor_code . ")"])->toArray()'
                            placeholder="Select Vendor..." />
                        <flux:error name="vendor_id" />
                    </flux:field>

                    <flux:field class="col-span-2">
                        <flux:label>Code</flux:label>
                        <flux:input type="text" wire:model="vendor_code" disabled placeholder="Vendor code automatically filled" />
                    </flux:field>

                    <flux:field class="col-span-2">
                        <flux:label>Vendor Address</flux:label>
                        <flux:textarea wire:model="vendor_address" rows="4" placeholder="Address of the vendor" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Phone Number</flux:label>
                        <flux:input type="text" wire:model="vendor_contact_phone" placeholder="Phone number of the vendor" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Email</flux:label>
                        <flux:input type="email" wire:model="vendor_contact_email" placeholder="Email of the vendor" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Payment Terms</flux:label>
                        <x-searchable-select wire:model.live="payment_terms" name="payment_terms" :value="$payment_terms"
                            :options='[["value" => "Net 30", "label" => "Net 30 (30 Days)"], ["value" => "Net 60", "label" => "Net 60 (60 Days)"], ["value" => "Due on Receipt", "label" => "Due on Receipt"], ["value" => "2% 10 Net 30", "label" => "2% 10 Net 30"], ["value" => "Cash on Delivery", "label" => "Cash on Delivery (COD)"]]'
                            placeholder="Select Payment Terms..." />
                    </flux:field>

                    <flux:field>
                        <flux:label>Delivery Terms</flux:label>
                        <x-searchable-select wire:model.live="delivery_terms" name="delivery_terms" :value="$delivery_terms"
                            :options='[["value" => "FOB Shipping Point", "label" => "FOB Shipping Point"], ["value" => "FOB Destination", "label" => "FOB Destination"], ["value" => "CIF", "label" => "CIF"], ["value" => "Ex-Works", "label" => "Ex-Works"]]'
                            placeholder="Select Delivery Terms..." />
                    </flux:field>
                </div>
            </x-ui.card>

            {{-- Shipping Information --}}
            <x-ui.card title="Shipping Information">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field class="md:col-span-2">
                        <flux:label>Warehouse*</flux:label>

                        <x-searchable-select
                            wire:model="warehouse_id"
                            :options="$warehouses->map(fn ($w) => [
                                'value' => $w->id,
                                'label' => $w->name,
                            ])->toArray()"
                            placeholder="Select Warehouse..."
                        />

                        <flux:error name="warehouse_id" />
                    </flux:field>

                    {{-- <flux:field>
                        <flux:label>Reference Number</flux:label>
                        <flux:input type="text" wire:model="reference_number" />
                    </flux:field> --}}

                    <flux:field class="md:col-span-2">
                        <flux:label>Warehouse Address</flux:label>
                        <flux:textarea
                            wire:model.defer="warehouse_address"
                            rows="3"
                            disabled
                        />
                    </flux:field>

                    <flux:field>
                        <flux:label>Warehouse Phone</flux:label>
                        <flux:input
                            type="text"
                            wire:model.defer="warehouse_phone"
                            disabled
                        />
                    </flux:field>

                    <flux:field>
                        <flux:label>Warehouse Email</flux:label>
                        <flux:input
                            type="email"
                            wire:model.defer="warehouse_email"
                            disabled
                        />
                    </flux:field>
                </div>
            </x-ui.card>
        </div>

        {{-- Items --}}
        <x-ui.card title="Items">
            <div class="flex items-center justify-end mb-4">
                <flux:button
                    variant="primary"
                    icon="plus"
                    wire:click.prevent="addItem"
                    size="sm"
                >
                    Add Item
                </flux:button>
            </div>

            <div class="space-y-4">
                @foreach ($items as $index => $item)
                    <div class="p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                        <div class="flex justify-between items-center mb-3">
                            <span class="font-medium text-sm">
                                Item {{ $index + 1 }}
                                @if (!empty($item['line_total']))
                                    - {{ $currency }} {{ number_format($item['line_total'], 2) }}
                                @endif
                            </span>

                            <flux:button
                                variant="ghost"
                                size="sm"
                                wire:click.prevent="removeItem({{ $index }})"
                                class="text-red-600"
                            >
                                Remove
                            </flux:button>
                        </div>

                        <div class="grid md:grid-cols-4 gap-4">
                            <div class="md:col-span-2">
                                <flux:field>
                                    <flux:label>Product *</flux:label>

                                    <x-searchable-select
                                        wire:model="items.{{ $index }}.product_id"
                                        :options="$products->map(fn ($p) => [
                                            'value' => $p->id,
                                            'label' => $p->name,
                                        ])->toArray()"
                                        placeholder="Select Product..."
                                    />

                                    <flux:error name="items.{{ $index }}.product_id" />
                                </flux:field>
                            </div>


                            <flux:field>
                                <flux:label>Quantity *</flux:label>
                                    <flux:input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0"
                                        wire:model.blur="items.{{ $index }}.requested_stock"
                                    />
                            </flux:field>

                            <flux:field>
                                <flux:label>Unit</flux:label>

                                <x-searchable-select
                                    wire:model="items.{{ $index }}.unit_id"
                                    :options="$units->map(fn ($u) => [
                                        'value' => $u->id,
                                        'label' => $u->name,
                                    ])->toArray()"
                                    placeholder="Select Unit..."
                                />

                                <flux:error name="items.{{ $index }}.unit_id" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Unit Price *</flux:label>

                                <div
                                    x-data="{
                                        raw: @entangle('items.' . $index . '.unit_price').live,
                                        currency: @entangle('currency'),

                                        get symbol() {
                                            switch (this.currency) {
                                                case 'USD': return '$';
                                                case 'SGD': return 'S$';
                                                default: return 'Rp';
                                            }
                                        },

                                        get formatted() {
                                            if (this.raw === null || this.raw === '') return '';
                                            return this.raw
                                                .toString()
                                                .replace(/\D/g, '')
                                                .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                        },

                                        update(e) {
                                            const val = e.target.value.replace(/\D/g, '');
                                            this.raw = val === '' ? 0 : Number(val);
                                        }
                                    }"
                                    >
                                    <flux:input.group>
                                        <flux:input.group.prefix x-text="symbol"></flux:input.group.prefix>

                                        <flux:input
                                            type="text"
                                            placeholder="0"
                                            x-bind:value="formatted"
                                            x-on:input="update($event)"
                                        />
                                    </flux:input.group>
                                </div>

                                <flux:error name="items.{{ $index }}.unit_price" />
                            </flux:field>


                            <flux:field>
                                <flux:label>Discount %</flux:label>
                                    <flux:input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        placeholder="0"
                                        wire:model.blur="items.{{ $index }}.discount_percent"
                                    />
                            </flux:field>

                            <flux:field>
                                <flux:label>Tax Code</flux:label>
                                <flux:input type="text" wire:model="items.{{ $index }}.tax_code" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Tax Amount</flux:label>
                                    <flux:input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0"
                                        wire:model.blur="items.{{ $index }}.tax_amount"
                                    />
                            </flux:field>

                            {{-- <div class="md:col-span-2">
                                <flux:field>
                                    <flux:label>Description</flux:label>
                                    <flux:input
                                        type="text"
                                        wire:model="items.{{ $index }}.item_description"
                                    />
                                </flux:field>
                            </div>

                            <flux:field>
                                <flux:label>Delivery Date</flux:label>
                                <flux:input
                                    type="date"
                                    wire:model="items.{{ $index }}.requested_delivery_date"
                                />
                            </flux:field>

                            <flux:field>
                                <flux:label>Bin Location</flux:label>
                                <flux:input
                                    type="text"
                                    wire:model="items.{{ $index }}.warehouse_bin_location"
                                />
                            </flux:field> --}}
                        </div>
                    </div>
                @endforeach

                @if (count($items) === 0)
                    <div class="text-center py-8 text-zinc-500">
                        No items added yet. Click "Add Item" to start.
                    </div>
                @endif
            </div>
        </x-ui.card>

        {{-- Totals --}}
        <x-ui.card title="Totals">
            <div class="grid md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <div class="flex justify-between py-2 border-b">
                        <span>Subtotal:</span>
                        <span class="font-medium">{{ $currency }} {{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b">
                        <span>Discount:</span>
                        <span class="font-medium text-red-600">-{{ $currency }} {{ number_format($total_discount, 2)
                            }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b">
                        <span>Tax:</span>
                        <span class="font-medium">{{ $currency }} {{ number_format($tax_total, 2) }}</span>
                    </div>
                </div>

                <div class="space-y-3">
                    <flux:field>
                        <flux:label>Freight Charges</flux:label>
                        <flux:input
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0"
                            wire:model.blur="freight_charges"
                        />
                    </flux:field>

                    <flux:field>
                        <flux:label>Other Charges</flux:label>
                        <flux:input
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0"
                            wire:model.blur="other_charges"
                        />
                    </flux:field>

                    <div class="flex justify-between py-3 border-t-2 border-zinc-400 dark:border-zinc-600">
                        <span class="text-lg font-bold">Grand Total:</span>
                        <span class="text-lg font-bold text-green-600">{{ $currency }} {{ number_format($grand_total, 2)
                            }}</span>
                    </div>
                </div>
            </div>
        </x-ui.card>

        {{-- Additional Information --}}
        <x-ui.card title="Additional Information">
            <div class="grid md:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Internal Notes</flux:label>
                    <flux:textarea wire:model="internal_notes" rows="3"
                        placeholder="For internal use (not visible to vendor)" />
                </flux:field>

                <flux:field>
                    <flux:label>Vendor Notes</flux:label>
                    <flux:textarea wire:model="vendor_notes" rows="3"
                        placeholder="Special instructions for vendor" />
                </flux:field>
            </div>
        </x-ui.card>

        {{-- Actions --}}
        <div class="sticky bottom-0 left-0 right-0 z-10 bg-white dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-800 pt-4 pb-4 -mx-6 px-6">
            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">
                    {{ $poId ? 'Update Purchase Order' : 'Save Purchase Order' }}
                </flux:button>
                <flux:button type="button" variant="ghost" wire:click="cancel">
                    Cancel
                </flux:button>
                <flux:spacer />
                <span class="text-xs text-zinc-500 self-center">* Required fields</span>
            </div>
        </div>
    </form>
</div>