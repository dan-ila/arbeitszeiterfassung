<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkBreak extends Model
{
    protected $table = 'breaks';

    protected $fillable = ['work_log_id', 'start_time', 'end_time', 'note'];

    public function workLog()
    {
        return $this->belongsTo(WorkLog::class);
    }
}
