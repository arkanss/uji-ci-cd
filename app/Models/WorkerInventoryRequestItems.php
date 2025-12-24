<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkerInventoryRequestItems extends Model
{
    use softDeletes;

    protected $table = 'worker_inventory_request_items';

    protected $primaryKey = 'id';
    public $incrementing = false; // Not auto-incrementing
    protected $keyType = 'string'; // UUID is string

    protected $fillable = [
        'product_id',
        'worker_inventory_request_id',
        'requested_stock',
        'returned_stock',
        'received_stock',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'requested_stock' => 'integer',
        'returned_stock' => 'integer',
        'received_stock' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(WorkerInventoryRequests::class, 'worker_inventory_request_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id')->withTrashed();
    }
}