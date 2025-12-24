<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Admin extends Authenticatable
{
    use Notifiable, SoftDeletes;

    /** @var string */
    protected $table = 'admins'; // pastikan tabel sesuai

    protected $primaryKey = 'id';
    public $incrementing = false; // UUID, bukan auto-increment
    protected $keyType = 'string'; // UUID adalah string

    /** @var array */
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'role',     // integer atau string, sesuai DB
        'phone',
        'avatar',
        'is_active',
    ];

    /** @var array */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'id' => 'string',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        // generate UUID jika belum ada
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Uuid::uuid7()->toString();
            }
        });
    }

    /**
     * Helper: cek apakah admin aktif
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Optional: helper cek role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }
}
