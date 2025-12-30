<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;

class ProductStockOverview extends Model
{
    use SoftDeletes;

    protected $table = 'product_stock_overview';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'stock_available',
        'stock_in_delivery',
        'bad_stock',
        'products_id',
    ];

    protected $casts = [
        'stock_available' => 'integer',
        'stock_in_delivery' => 'integer',
        'bad_stock' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'products_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = Uuid::uuid4()->toString();
            }
        });
    }

}