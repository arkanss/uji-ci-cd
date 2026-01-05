<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Ramsey\Uuid\Uuid;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $primaryKey = 'id';
    public $incrementing = false; // Not auto-incrementing
    protected $keyType = 'string'; // UUID is string

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password_hash',
        'phone_number',
        'role',
        'gender',
        'date_of_birth',
        'user_code',
        'access_code',
        'auth_using_access_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_hash' => 'hashed',
            'date_of_birth' => 'date',
        ];
    }

    public function getAuthPasswordName()
    {
        return 'password_hash';
    }

    public function isAdmin()
    {
        return $this->role === 2;
    }

    public function isAccounting()
    {
        return $this->role === 7;
    }

    public function isAdminWarehouse()
    {
        return $this->role === 8;
    }

    public function isWarehouse()
    {
        return $this->role === 9;
    }

    public function canAccessMenu($menu)
    {
        $permissions = [
            'dashboard' => [2, 7, 8, 9],
            'purchase-order' => [2, 7],
            'operational-cost' => [2, 7],
            'receive-goods' => [2, 8],
            'delivery' => [2, 8],
            'stock' => [2, 8],
            'outlet-order' => [2, 7, 8],
            // 'receive_po' => [2, 7],
            // 'admin' => [2],
            // 'csv_reports' => [2, 7],
            // 'operational' => [2, 7, 8],
            // 'dp_item_requests' => [2, 7, 8],
            // 'operational_costs' => [2, 7],
            // 'point_management' => [2, 7],
            // 'purchase_orders' => [2, 7, 8, 9],
            // 'merchants' => [2, 7],
            // 'challenges' => [2, 7],
            // 'products' => [2, 8, 9],
            // 'product_categories' => [2, 8, 9],
            // 'product_rewards' => [2, 7],
            // 'product_distribution' => [2, 8, 9],
            // 'product_stock_history' => [2, 8, 9],
        ];

        return in_array($this->role, $permissions[$menu] ?? []);
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Uuid::uuid7()->toString();
            }
        });
    }

    public function signatures()
    {
        return $this->hasMany(UserSignature::class, 'user_id');
    }

    public function latestSignature()
    {
        return $this->hasOne(UserSignature::class, 'user_id', 'id')
                    ->orderByDesc('created_at'); // ambil terbaru berdasarkan waktu
    }
}
