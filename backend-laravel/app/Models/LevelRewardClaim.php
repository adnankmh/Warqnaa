<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LevelRewardClaim extends Model
{
    protected $fillable = ['user_id','level','reward_type','amount','reward_payload'];
    protected $casts = ['reward_payload' => 'array'];
    public function user() { return $this->belongsTo(User::class); }
}
