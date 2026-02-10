<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'student_id_no',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'department',
        'program',
        'company',
        'company_address',
        'supervisor_name',
        'ojt_start',
        'ojt_end',
        'required_hours',
        'contact_no',
        'status',
    ];

    protected $casts = [
        'ojt_start' => 'date',
        'ojt_end' => 'date',
        'required_hours' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class, 'student_id');
    }

    public function logOverrides(): HasMany
    {
        return $this->hasMany(LogOverride::class, 'student_id');
    }

    public function getFullNameAttribute(): string
    {
        $name = $this->first_name;
        if ($this->middle_name) {
            $name .= ' ' . $this->middle_name;
        }
        $name .= ' ' . $this->last_name;
        if ($this->suffix) {
            $name .= ' ' . $this->suffix;
        }
        return $name;
    }
}
