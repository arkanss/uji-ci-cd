<?php

namespace App\Models;

use App\Enums\ProductDistributionPaymentStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid;

class ProductDistributionPayment extends Model
{
    use SoftDeletes;

    protected $table = 'product_distribution_payments';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'proof_of_payment',
        'reject_reason',
        'status',
        'product_distribution_id',
    ];

    protected $casts = [
        'status' => ProductDistributionPaymentStatusEnum::class,
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

    public function productDistribution(): BelongsTo
    {
        return $this->belongsTo(ProductDistribution::class, 'product_distribution_id');
    }
}
