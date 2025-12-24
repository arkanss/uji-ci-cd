<?php

namespace App\Models;

use App\Enums\DPItemRequestsEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkerInventoryRequests extends Model
{
    use softDeletes;

    protected $table = 'worker_inventory_requests';
    
    protected $primaryKey = 'id';
    public $incrementing = false; // Not auto-incrementing
    protected $keyType = 'string'; // UUID is string

    protected $fillable = [
        'code',
        'status',
        'user_id',
        'verified_by',
        'opened_verified_by',
        'closed_verified_by',
        'opened_at',
        'closed_at'
    ];

    protected $casts = [
        'status' => DPItemRequestsEnum::class,
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
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

    public function items(): HasMany
    {
        return $this->hasMany(WorkerInventoryRequestItems::class, 'worker_inventory_request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function openedVerifyBy()
    {
        return $this->belongsTo(User::class, 'opened_verified_by');
    }

    public function closedVerifyBy()
    {
        return $this->belongsTo(User::class, 'closed_verified_by');
    }

}
