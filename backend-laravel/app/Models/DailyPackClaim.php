<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DailyPackClaim extends Model {
    protected $fillable=['user_id','claim_date','reward_type','reward_key','duration_hours','expires_at','payload'];
    protected $casts=['claim_date'=>'date','expires_at'=>'datetime','payload'=>'array'];
    public function user(){ return $this->belongsTo(User::class); }
}
