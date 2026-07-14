<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengeRun extends Model
{
    protected $fillable = [
        'user_id','game_key','stages_total','current_stage','attempts_left','status',
        'current_opponent_user_id','current_opponent_name','stage_rewards','claimed_stages',
        'last_result','last_client_result_id','processed_result_ids','completed_at',
    ];

    protected $casts = [
        'stage_rewards' => 'array',
        'claimed_stages' => 'array',
        'processed_result_ids' => 'array',
        'completed_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function opponent() { return $this->belongsTo(User::class, 'current_opponent_user_id'); }
}
