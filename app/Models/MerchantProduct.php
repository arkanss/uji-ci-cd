<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Ramsey\Uuid\Uuid;

class MerchantProduct extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;

    protected $primaryKey = 'id';
    public $incrementing = false; // Not auto-incrementing
    protected $keyType = 'string'; // UUID is string

    protected $fillable = [
        'merchant_id',
        'product_id',
        'stock',
        'status',
        'total_stock',
    ];

    protected $casts = [
        'stock' => 'integer',
        'total_stock' => 'integer',
        'status' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Uuid::uuid7()->toString();
            }
            // Set default status if not provided
            if (is_null($model->status)) {
                $model->status = self::STATUS_ACTIVE;
            }
        });
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function stockHistories()
    {
        return $this->hasMany(ProductStockHistory::class, 'merchant_id', 'merchant_id')
            ->where('product_id', $this->product_id);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            default => 'Unknown',
        };
    }

    /**
     * Check if merchant product is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Get available status options
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }
}
