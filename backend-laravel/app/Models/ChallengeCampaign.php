<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ChallengeCampaign extends Model
{
    protected $fillable=['key','name','stage_count','starting_lives','active','rewards'];
    protected $casts=['name'=>'array','active'=>'boolean','rewards'=>'array'];
    public function runs(){ return $this->hasMany(ChallengeRun::class); }
}
