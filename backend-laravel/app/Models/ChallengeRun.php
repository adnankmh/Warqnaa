<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengeRun extends Model
{
    protected $fillable = ['user_id','game_key','stage','lives','stages_total','status','claimed_stages','completed_at'];
    protected $casts = ['claimed_stages'=>'array','completed_at'=>'datetime'];
    public function user(){ return $this->belongsTo(User::class); }
}
