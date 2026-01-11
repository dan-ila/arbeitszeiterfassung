<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkTimeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_log_id',
        'type',
        'status',
        'requested_clock_in',
        'requested_clock_out',
        'requested_break_minutes',
        'reason',
        'admin_comment',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'requested_clock_in' => 'datetime',
        'requested_clock_out' => 'datetime',
        'requested_break_minutes' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workLog()
    {
        return $this->belongsTo(WorkLog::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
