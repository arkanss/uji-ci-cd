<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid;

class ChallengeReward extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = false; // Not auto-incrementing
    protected $keyType = 'string'; // UUID is string

    protected $fillable = [
        'challenge_id',
        'reward_type',
        'reward_amount',
        'point_id',
        'voucher_id',
        'product_id',
    ];

    protected $casts = [
        'reward_type' => 'integer',
        'reward_amount' => 'integer',
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

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function point(): BelongsTo
    {
        return $this->belongsTo(Point::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Voucher relation - add when Voucher model is created
    // public function voucher(): BelongsTo
    // {
    //     return $this->belongsTo(Voucher::class);
    // }
}
