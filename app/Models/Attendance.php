<?php

namespace App\Models;

use Database\Factories\AttendanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    /** @use HasFactory<AttendanceFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_date',
        'work_location',
        'is_late',
        'check_in_at',
        'check_out_at',
        'lunch_started_at',
        'lunch_ended_at',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'is_late' => 'boolean',
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
            'lunch_started_at' => 'datetime',
            'lunch_ended_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
