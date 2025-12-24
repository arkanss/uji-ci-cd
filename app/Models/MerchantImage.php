<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Psy\Util\Str;
use Ramsey\Uuid\Uuid;

class MerchantImage extends Model
{
    use SoftDeletes;

    protected $table = 'merchant_images';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'merchant_id',
        'image',
        'ordering'
    ];

     protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = Uuid::uuid7()->toString();
            }
        });
    }

    // Relasi ke merchant
    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id');
    }
}
