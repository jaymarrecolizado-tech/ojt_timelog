<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeLog extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'student_id',
        'log_type',
        'log_category',
        'timestamp',
        'date',
        'qr_token_hash',
        'location_id',
        'latitude',
        'longitude',
        'device_info',
        'ip_address',
        'is_manual',
        'is_flagged',
        'flag_reason',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'date' => 'date',
        'is_manual' => 'boolean',
        'is_flagged' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function logOverride(): BelongsTo
    {
        return $this->belongsTo(LogOverride::class, 'time_log_id');
    }
}
