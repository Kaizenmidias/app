<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProspectingExport extends Model
{
    protected $fillable = ['user_id','prospecting_job_id','format'];
    public function job(){ return $this->belongsTo(ProspectingJob::class, 'prospecting_job_id'); }
}
