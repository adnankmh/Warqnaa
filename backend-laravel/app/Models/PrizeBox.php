<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrizeBox extends Model
{
    protected $fillable = [
        'user_id',
        'box_key',
        'source_type',
        'source_key',
        'awarded_date',
        'opened_at',
        'reward_type',
        'reward_key',
        'duration_hours',
        'expires_at',
        'payload',
    ];

    protected $casts = [
        'awarded_date' => 'date:Y-m-d',
        'opened_at' => 'datetime',
        'expires_at' => 'datetime',
        'payload' => 'array',
        'duration_hours' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
