<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProspectingJobAttempt extends Model
{
    protected $fillable = ['prospecting_job_id', 'provider', 'external_id', 'status', 'request_hash', 'error_message', 'started_at', 'finished_at'];
    protected $casts = ['started_at' => 'datetime', 'finished_at' => 'datetime'];
    public function job() { return $this->belongsTo(ProspectingJob::class, 'prospecting_job_id'); }
}
