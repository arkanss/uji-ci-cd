<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\PointStatusEnum;
use Ramsey\Uuid\Uuid;

class Point extends Model
{
    use SoftDeletes;

    protected $table = 'points';

    protected $primaryKey = 'id';
    public $incrementing = false; // Not auto-incrementing
    protected $keyType = 'string'; // UUID is string

    protected $fillable = [
        'id',
        'icon',
        'key',
        'name',
        'abbr',
        'value_idr',
        'parent_id',
        'status',
        'is_exchangeable',
        'scope_service',
    ];

    protected $casts = [
        'id' => 'string',
        'value_idr' => 'decimal:3',
        'is_exchangeable' => 'boolean',
        'status' => PointStatusEnum::class,
        'scope_service' => 'array'
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Point::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Point::class, 'parent_id');
    }
}
