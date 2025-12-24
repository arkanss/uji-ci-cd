<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\ChallengeTypeEnum;
use App\Enums\ChallengeTriggerTypeEnum;
use App\Enums\ChallengeStatusEnum;
use Ramsey\Uuid\Uuid;

class Challenge extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = false; // Not auto-incrementing
    protected $keyType = 'string'; // UUID is string

    protected $fillable = [
        'name',
        'description',
        'image',
        'type',
        'trigger_type',
        'target',
        'start_date',
        'end_date',
        'status',
        'term_and_condition',
        'how_to_join',
    ];

    protected $casts = [
        'type' => ChallengeTypeEnum::class,
        'trigger_type' => ChallengeTriggerTypeEnum::class,
        'status' => ChallengeStatusEnum::class,
        'target' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
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

    public function rewards(): HasMany
    {
        return $this->hasMany(ChallengeReward::class);
    }
}
