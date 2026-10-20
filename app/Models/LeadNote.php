<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LeadNote extends Model { protected $fillable=['user_id','lead_id','content']; public function lead(){return $this->belongsTo(Lead::class);} }
