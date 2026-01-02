<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PurchaseOrderItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'purchase_order_items';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'purchase_order_id',
        'product_id',
        'item_description',
        'uom',
        'requested_stock',
        'received_stock',
        'unit_id',
        'unit_price',
        'discount_percent',
        'discount_amount',
        'tax_code',
        'tax_amount',
        'line_total',
        'requested_delivery_date',
        'warehouse_bin_location',
    ];

    protected $casts = [
        'requested_stock' => 'decimal:2',
        'received_stock' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
        'requested_delivery_date' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    /**
     * Calculate line total based on quantity, unit price, discount, and tax
     */
    public function calculateLineTotal()
    {
        $subtotal = ($this->requested_stock ?? 0) * ($this->unit_price ?? 0);
        $discountAmount = $this->discount_amount ?? (($subtotal * ($this->discount_percent ?? 0)) / 100);
        $taxableAmount = $subtotal - $discountAmount;
        $taxAmount = $this->tax_amount ?? 0;

        return $subtotal - $discountAmount + $taxAmount;
    }
}

