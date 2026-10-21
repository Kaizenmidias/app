<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class LeadList extends Model { protected $fillable=['user_id','name']; public function user(){return $this->belongsTo(User::class);} public function leads(){return $this->hasMany(Lead::class);} }
