<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vendors';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'vendor_code',
        'name',
        'email',
        'phone',
        'billing_address',
        'shipping_address',
        'payment_terms',
        'currency',
        'delivery_terms',
        'tax_id',
        'contact_person',
        'contact_phone',
        'contact_email',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            // Auto-generate vendor code if not provided
            if (empty($model->vendor_code)) {
                $prefix = 'VND';
                $lastVendor = self::where('vendor_code', 'like', "{$prefix}%")
                    ->orderBy('vendor_code', 'desc')
                    ->first();

                $seq = 1;
                if ($lastVendor) {
                    $lastSeq = (int) substr($lastVendor->vendor_code, -4);
                    $seq = $lastSeq + 1;
                }
                $model->vendor_code = $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Purchase orders for this vendor
     */
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'vendor_id');
    }
}
