<?php

namespace App\Models;

use App\Enums\OrderRequestEnum;
use App\Enums\OrderTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ProductDistributionPayment;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductDistribution extends Model
{
    use softDeletes;

    protected $table = 'product_distributions';

    protected $primaryKey = 'id';
    public $incrementing = false; // Not auto-incrementing
    protected $keyType = 'string'; // UUID is string

    protected $fillable = [
        'code',
        'status',
        'order_type',
        'requested_by',
        'verified_by',
        'user_id',
        'merchant_repository_id',
        'reject_reason',
        'product_distribution_delivery_id',
        'is_paid',
        'total_price',
        'total_paid',
        'pending_price',
        'estimated_delivery_date',
        'delivered_at',
        'proof_of_delivered',
    ];

    protected $casts = [
        'status' => OrderRequestEnum::class,
        'order_type' => OrderTypeEnum::class,
        'is_paid' => 'boolean',
        'total_price' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'pending_price' => 'integer',
        'estimated_delivery_date' => 'datetime',
        'delivered_at' => 'datetime',        
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

    protected static function booted()
    {
        static::saving(function ($model) {
            if ($model->status === OrderRequestEnum::Verified) {
                $model->reject_reason = null;
            }
        });

        static::updated(function ($model) {
            try {
                $payment = $model->latestPayment();

                if ($model->status === OrderRequestEnum::Delivered) {

                    if ($payment && $payment->status === \App\Enums\ProductDistributionPaymentStatusEnum::Paid) {

                        DB::transaction(function () use ($model) {

                            $model = ProductDistribution::lockForUpdate()->find($model->id);

                            if ($model->status === OrderRequestEnum::Delivered) {
                                $model->update([
                                    'status' => OrderRequestEnum::Completed,
                                    'delivered_at' => now(),
                                ]);
                            }
                        });
                    }
                }
            } catch (\Throwable $e) {
                Log::error("Auto complete PO failed: " . $e->getMessage());
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductDistributionItems::class, 'product_distribution_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ProductDistributionPayment::class, 'product_distribution_id')
            ->orderBy('created_at', 'desc');
    }

    public function latestPayment()
    {
        return $this->payments()->orderBy('created_at', 'desc')->first();
    }

    public function delivery()
    {
        return $this->belongsTo(ProductDistributionDeliver::class, 'product_distribution_delivery_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function outlet()
    {
        return $this->belongsTo(
            Merchant::class,
            'user_id', // Foreign Key di product_distributions
            'user_id'  // Primary Key di merchants
        );
    }

    // Accessor for Filament table: human readable payment status from latest related payment
    public function getPaymentStatusLabelAttribute()
    {
        $payment = $this->latestPayment();
        if (! $payment) {
            return '-';
        }

        // status is cast to enum in ProductDistributionPayment model
        return $payment->status?->getLabel() ?? '-';
    }
}
