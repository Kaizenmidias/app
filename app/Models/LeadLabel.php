<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LeadLabel extends Model { protected $fillable=['user_id','name','background_color','text_color']; public function leads(){return $this->belongsToMany(Lead::class,'lead_label_lead');} }
