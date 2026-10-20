<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AdminAuditLog extends Model { public $timestamps=false; protected $fillable=['admin_user_id','action','target_type','target_id','metadata','created_at']; protected $casts=['metadata'=>'array','created_at'=>'datetime']; }
