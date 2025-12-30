<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid;

class ProductStockHistory extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false; // Not auto-incrementing
    protected $keyType = 'string'; // UUID is string

    const UPDATED_AT = null;

    protected $fillable = [
        'product_id',
        'stock',
        'stock_before',
        'stock_after',
        'merchant_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function merchant(): BelongsTo
    {
        // Merchant primary key is `user_id`, specify owner key to avoid incorrect `id` usage
        return $this->belongsTo(Merchant::class, 'merchant_id', 'user_id');
    }
}
