<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ChallengeProgress extends Model {
    protected $table='challenge_progress';
    protected $fillable=['user_id','challenge_definition_id','progress','period_key','claimed_at','payload'];
    protected $casts=['claimed_at'=>'datetime','payload'=>'array'];
    public function definition(){ return $this->belongsTo(ChallengeDefinition::class,'challenge_definition_id'); }
}
