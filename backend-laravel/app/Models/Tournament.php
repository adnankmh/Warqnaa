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
    ];

    protected $fillable = [
        'creator_id','club_id','game_id','stages','seats_per_match','entry_fee',
        'prize_pool','status','bracket','house_cut_percent','prize_distribution',
        'leaderboard_points','reward_multiplier','sponsored',
    ];

    public function creator(){ return $this->belongsTo(User::class,'creator_id'); }
    public function game(){ return $this->belongsTo(Game::class); }
    public function club(){ return $this->belongsTo(Club::class); }
    public function entries(){ return $this->hasMany(TournamentEntry::class); }
}
