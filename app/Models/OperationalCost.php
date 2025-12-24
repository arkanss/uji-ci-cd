<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ProductDistributionPayment;
use Highlight\Mode;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationalCost extends Model
{
    use SoftDeletes;

    protected $table = 'operational_costs';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'amount',
        'attachments_url',
        'date',
        'created_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = Uuid::uuid4()->toString();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}