<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Ramsey\Uuid\Uuid;

class Merchant extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'name',
        'profile_picture',
        'address',
        'full_address',
        'area_province_id',
        'area_city_id',
        'area_district_id',
        'area_sub_district_id',
        'open_time',
        'close_time',
        'status',
        'point_balance',
        'date_of_birth',
        'land_level',
        'gender',
        'code',
        'description',
        'capacity',
        'is_hangout_place',
        'free_wifi',
        'smoking_area',
        'private_room',
        'live_music',
        'selling_cigarettes',
        'can_reserve',
        'toilet_available',
        'selling_merchandise',
        'dealing_status',
    ];

    protected $casts = [
        'open_time' => 'datetime:H:i',
        'close_time' => 'datetime:H:i',
        'date_of_birth' => 'date',
        'point_balance' => 'decimal:2',
    ];

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($model) {
    //         if (empty($model->id)) {
    //             $model->id = Uuid::uuid7()->toString();
    //         }
    //     });
    // }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->search_keywords = implode(' ', [
                $model->name,
                $model->address,
                $model->full_address,
                $model->code,
                $model->description,
            ]);
        });
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function images()
    {
        return $this->hasMany(MerchantImage::class, 'merchant_id');
    }

    public function employees()
    {
        return $this->hasMany(MerchantEmployee::class, 'merchant_id');
    }

    public function products()
    {
        return $this->hasMany(MerchantProduct::class, 'merchant_id');
    }
}
