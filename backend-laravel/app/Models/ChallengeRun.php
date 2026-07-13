<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ChallengeRun extends Model
{
    protected $fillable=['user_id','challenge_campaign_id','game_key','stage','lives','wins','losses','status','opponent_user_id','claimed_stages','history','completed_at'];
    protected $casts=['claimed_stages'=>'array','history'=>'array','completed_at'=>'datetime'];
    public function user(){ return $this->belongsTo(User::class); }
    public function campaign(){ return $this->belongsTo(ChallengeCampaign::class,'challenge_campaign_id'); }
    public function opponent(){ return $this->belongsTo(User::class,'opponent_user_id'); }
}
