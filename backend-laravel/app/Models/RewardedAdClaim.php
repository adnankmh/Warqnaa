<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardedAdClaim extends Model
{
    protected $fillable = ['user_id','claim_date','reward_tokens','reward_xp','network','verification_id','payload'];
    protected $casts = ['claim_date'=>'date','payload'=>'array'];

    public function user(){ return $this->belongsTo(User::class); }
}
