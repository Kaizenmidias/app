<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes;
class ProspectingJob extends Model { use SoftDeletes; protected $fillable=['user_id','scraper_job_id','keywords','location','latitude','longitude','search_parameters','status','progress','scraper_slot_acquired','cancel_requested_at','error_message','started_at','finished_at']; protected $casts=['keywords'=>'array','search_parameters'=>'array','started_at'=>'datetime','finished_at'=>'datetime','cancel_requested_at'=>'datetime','scraper_slot_acquired'=>'boolean']; public function user(){return $this->belongsTo(User::class);} public function leads(){return $this->hasMany(Lead::class);} }
