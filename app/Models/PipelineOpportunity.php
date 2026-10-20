<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; class PipelineOpportunity extends Model { protected $fillable=['user_id','lead_id','pipeline_stage_id']; public function lead(){return $this->belongsTo(Lead::class);} public function stage(){return $this->belongsTo(PipelineStage::class,'pipeline_stage_id');} }
