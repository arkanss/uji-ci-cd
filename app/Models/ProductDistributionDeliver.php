<?php

namespace App\Models;

use App\Enums\ProductDistributionDeliver as EnumsProductDistributionDeliver;
use App\Enums\ProductDistributionDeliverEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class ProductDistributionDeliver extends Model
{
    use SoftDeletes;

    protected $table = 'product_distribution_deliveries';

    protected $primaryKey = 'id';
    public $incrementing = false; // Not auto-incrementing
    protected $keyType = 'string'; // UUID is string

    protected $fillable = [
        'code',
        'status',
        'driver_id',
        'date',
    ];

    protected $casts = [
        'date' => 'datetime',
        'status' => ProductDistributionDeliverEnum::class,
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

    public function distributions(): HasMany
    {
        return $this->hasMany(ProductDistribution::class, 'product_distribution_delivery_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
