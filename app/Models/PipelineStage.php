<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; class PipelineStage extends Model { protected $fillable=['user_id','name','position']; public function opportunities(){return $this->hasMany(PipelineOpportunity::class);} public function user(){return $this->belongsTo(User::class);} }
