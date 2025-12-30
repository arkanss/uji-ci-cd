<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;

class Product extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = false; // Not auto-incrementing
    protected $keyType = 'string'; // UUID is string

    protected $fillable = [
        'name',
        'price',
        'discount',
        'description',
        'image',
        'point_value',
        'category_id',
        'brand',
        'code',
        'sku',
        'featured',
        'stock',
        'reward_type',
        // 'reward_reference_id',
        'level_zero_reward',
        'level_one_reward',
        'scope_service',
        'scope_type',
        'weight',
        'height',
        'length',
        'width',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'point_value' => 'integer',
        'featured' => 'boolean',
        'stock' => 'integer',
        'reward_type' => 'integer',
        'level_zero_reward' => 'integer',
        'level_one_reward' => 'integer',
        'scope_service' => 'array',
        'scope_type' => 'array',
        'weight' => 'integer',
        'height' => 'integer',
        'length' => 'integer',
        'width' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'scope_service' => 'array',
        'scope_type' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Uuid::uuid7()->toString();
            }
        });
    }

    public function distributionItems()
    {
        return $this->hasMany(\App\Models\ProductDistributionItems::class, 'product_id');
    }

    public function getStockInDeliveryAttribute()
    {
        return $this->distributionItems()->sum('approved_stock');
    }


    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function stockHistories(): HasMany
    {
        return $this->hasMany(ProductStockHistory::class);
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(ProductReward::class);
    }

    public function stockOverview()
    {
        return $this->hasOne(ProductStockOverview::class, 'products_id');
    }

    public function merchantProducts(): HasMany
    {
        return $this->hasMany(MerchantProduct::class);
    }
}
