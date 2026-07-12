<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    protected $casts = [
        'bracket'=>'array',
        'prize_distribution'=>'array',
        'leaderboard_points'=>'array',
        'sponsored'=>'boolean',
        'reward_multiplier'=>'float',
        'name'=>'array','description'=>'array','settings'=>'array',
        'starts_at'=>'datetime','auto_accept'=>'boolean','random_seating'=>'boolean','chat_enabled'=>'boolean','ad_entry_enabled'=>'boolean','featured'=>'boolean',
    ];

    protected $fillable = [
        'creator_id','club_id','game_id','stages','seats_per_match','entry_fee',
        'prize_pool','status','bracket','house_cut_percent','prize_distribution',
        'leaderboard_points','reward_multiplier','sponsored','key','name','description','max_players','rounds','starts_at','auto_accept','random_seating','chat_enabled','turn_seconds','entry_mode','ad_entry_enabled','featured','settings',
    ];

    public function creator(){ return $this->belongsTo(User::class,'creator_id'); }
    public function game(){ return $this->belongsTo(Game::class); }
    public function club(){ return $this->belongsTo(Club::class); }
    public function entries(){ return $this->hasMany(TournamentEntry::class); }
}
