<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid;
use App\Models\RewardableEntity;

class ProductReward extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = false; // Not auto-incrementing
    protected $keyType = 'string'; // UUID is string

    protected $fillable = [
        'product_id',
        'amount',
        'reward_type',
        'reward_id',
        'rewardable_entity_id',
        'level',
        'direction',
    ];

    protected $casts = [
        'amount' => 'integer',
        'reward_type' => 'integer',
        'level' => 'integer',
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
            if (empty($model->reward_id)) {
                $model->reward_id = Uuid::uuid4()->toString();
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function rewardableEntity(): BelongsTo
    {
        return $this->belongsTo(RewardableEntity::class, 'rewardable_entity_id');
    }
}
