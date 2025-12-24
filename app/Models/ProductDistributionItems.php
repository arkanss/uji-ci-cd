<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductDistributionItems extends Model
{
    use SoftDeletes;

    protected $table = 'product_distribution_items';

    protected $primaryKey = 'id';
    public $incrementing = false; // Not auto-incrementing
    protected $keyType = 'string'; // UUID is string    

    protected $fillable = [
        'product_distribution_id',
        'product_id',
        'requested_stock',
        'approved_stock',
        'unit_id',
    ];

    protected $casts = [
        'requested_stock' => 'integer',
        'approved_stock' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(ProductDistribution::class, 'product_distribution_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function getRequestedStockInPcsAttribute():int
    {
        return $this->requested_stock * ($this->unit->ratio_to_pcs ?? 1);
    }

    public function getApprovedStockInPcsAttribute():int
    {
        return ($this->approved_stock ?? 0) * ($this->unit->ratio_to_pcs ?? 1);
    }
}
