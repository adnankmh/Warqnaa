<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProgressionEvent extends Model {
    protected $fillable=['user_id','room_id','event_key','event_type','mode','base_points','multiplier','awarded_xp','round_points','tournament_points','club_points','meta'];
    protected $casts=['meta'=>'array','multiplier'=>'float'];
}
