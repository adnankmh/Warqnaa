<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LuckyWheelSpin extends Model
{
    protected $fillable = [
        'user_id', 'spin_date', 'source', 'segment_key', 'segment_index',
        'token_cost', 'prize_box_id', 'reward',
    ];

    protected $casts = [
        'spin_date' => 'date:Y-m-d',
        'segment_index' => 'integer',
        'token_cost' => 'integer',
        'reward' => 'array',
    ];

    public function user(){ return $this->belongsTo(User::class); }
    public function prizeBox(){ return $this->belongsTo(PrizeBox::class); }
}
