<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Lead extends Model { protected $fillable=['user_id','prospecting_job_id','name','phone','email','website','category','address','latitude','longitude','source','external_place_id','raw_data']; protected $casts=['raw_data'=>'array']; public function user(){return $this->belongsTo(User::class);} public function job(){return $this->belongsTo(ProspectingJob::class,'prospecting_job_id');} public function notes(){return $this->hasMany(LeadNote::class);} }
