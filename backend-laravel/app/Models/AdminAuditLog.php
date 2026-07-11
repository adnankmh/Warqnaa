<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAuditLog extends Model
{
    protected $fillable = ['admin_id','action','target_type','target_id','before','after','meta','request_id','ip'];
    protected $casts = ['before'=>'array','after'=>'array','meta'=>'array'];
    public function admin(){ return $this->belongsTo(User::class,'admin_id'); }
}
