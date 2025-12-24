<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class ScheduledCsvData extends Model
{
    protected $table = 'scheduled_csv_data';
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'job_id',
        'job_type',
        'data',
        'status',
        'remarks',
        'result',
    ];

    protected $casts = [
        'data' => 'array',
        'result' => 'array',
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
    public function csvJob()
    {
        return $this->belongsTo(ScheduledCsvJob::class, 'job_id');
    }

    // Accessors
    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            0 => 'PENDING',
            1 => 'PROCESSING',
            2 => 'COMPLETED',
            4 => 'FAILED',
            default => 'UNKNOWN',
        };
    }

    public function getDataNameAttribute(): string
    {
        if (is_array($this->data) && isset($this->data['name'])) {
            return $this->data['name'];
        }
        return 'N/A';
    }
}
