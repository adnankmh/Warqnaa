<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserReport extends Model
{
    protected $fillable = [
        'reporter_id','reported_user_id','room_id','message_id','category','details','evidence',
        'status','reviewed_by','reviewed_at','resolution',
    ];
    protected $casts = ['evidence'=>'array','reviewed_at'=>'datetime'];
    public function reporter(){ return $this->belongsTo(User::class,'reporter_id'); }
    public function reportedUser(){ return $this->belongsTo(User::class,'reported_user_id'); }
    public function reviewer(){ return $this->belongsTo(User::class,'reviewed_by'); }
}
