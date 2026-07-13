<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ChallengeDefinition extends Model {
    protected $fillable=['key','name','description','cadence','metric','target','reward_tokens','reward_xp','settings','active','sort_order'];
    protected $casts=['name'=>'array','description'=>'array','settings'=>'array','active'=>'boolean'];
}
