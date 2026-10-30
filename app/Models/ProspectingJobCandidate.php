<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProspectingJobCandidate extends Model
{
    protected $fillable = ['prospecting_job_id', 'lead_id', 'external_place_id', 'disposition'];
    public function job() { return $this->belongsTo(ProspectingJob::class, 'prospecting_job_id'); }
    public function lead() { return $this->belongsTo(Lead::class); }
}
