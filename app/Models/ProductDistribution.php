<?php

namespace App\Models;

use App\Enums\OrderRequestEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ProductDistributionPayment;
use Illuminate\Database\Eloquent\Relations\HasOne;


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
        'requested_by',
        'verified_by',
        'reject_reason',
        'product_distribution_delivery_id',
    ];

    protected $casts = [
        'status' => OrderRequestEnum::class,
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

        // When status becomes Delivered, and latest payment is Paid, auto-mark Completed
        static::updated(function ($model) {
            try {
                if ($model->status === OrderRequestEnum::Delivered) {
                    $payment = $model->latestPayment();
                    if ($payment && $payment->status === \App\Enums\ProductDistributionPaymentStatusEnum::Paid) {
                        $model->update(['status' => OrderRequestEnum::Completed]);
                    }
                }
            } catch (\Throwable $e) {
                // swallow errors to avoid interrupting save flow; optionally log
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
