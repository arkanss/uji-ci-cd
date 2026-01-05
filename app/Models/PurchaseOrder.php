<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class PurchaseOrder extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'purchase_orders';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'uuid',
        'po_number',
        'date',
        'expected_delivery_date',
        'warehouse_id',
        'status',
        'vendor_id',
        'vendor_code',
        'vendor_address',
        'ship_to_address',
        'currency',
        'payment_terms',
        'delivery_terms',
        'subtotal',
        'total_discount',
        'tax_total',
        'freight_charges',
        'other_charges',
        'grand_total',
        'internal_notes',
        'vendor_notes',
        'reference_number',
        'approved_by',
        'approval_date',
        'attachments',
        'created_by',
        'completed_by',
        'completed_at',
    ];

    protected $casts = [
        'status' => \App\Enums\PurchaseOrderStatusEnum::class,
        'date' => 'datetime',
        'expected_delivery_date' => 'datetime',
        'approval_date' => 'datetime',
        'completed_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'freight_charges' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'attachments' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            // only set uuid if column exists to avoid errors on databases without the column yet
            try {
                if (Schema::hasColumn((new self)->getTable(), 'uuid') && empty($model->uuid)) {
                    $model->uuid = (string) Str::uuid();
                }
            } catch (\Throwable $e) {
                // ignore schema checks failures (e.g., during migrations)
            }
        });
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    

    /**
     * Return items with product and unit eager-loaded.
     */
    public function itemsWithDetails()
    {
        return $this->items()->with(['product', 'unit'])->get();
    }
}
