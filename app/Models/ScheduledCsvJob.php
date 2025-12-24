<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class ScheduledCsvJob extends Model
{
    protected $table = 'scheduled_csv_jobs';
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'file_name',
        'type',
        'status',
        'remarks',
        'result_file_name',
        'total_records',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

    // Relationships
    public function csvData()
    {
        return $this->hasMany(ScheduledCsvData::class, 'job_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessors
    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            1 => 'CUSTOMER',
            2 => 'MERCHANT',
            3 => 'PRODUCT',
            default => 'UNKNOWN',
        };
    }

    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            0 => 'PENDING',
            1 => 'PROCESSING',
            2 => 'COMPLETED',
            3 => 'COMPLETED_WITH_ERROR',
            4 => 'FAILED',
            default => 'UNKNOWN',
        };
    }

    public function getTotalSuccessAttribute(): int
    {
        return $this->csvData()->where('status', 2)->count();
    }

    public function getTotalFailedAttribute(): int
    {
        return $this->csvData()->where('status', 4)->count();
    }

    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_name) {
            return null;
        }

        // Assuming files are stored in storage/app/public
        // Adjust this based on your actual file storage configuration
        return config('app.api_url') . '/storage/' . $this->file_name;
    }
}
