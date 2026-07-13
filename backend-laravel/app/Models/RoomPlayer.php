<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomPlayer extends Model
{
    protected $fillable = [
        'room_id',
        'user_id',
        'bot_key',
        'seat',
        'is_bot',
        'connected',
        'missed_turns',
        'voluntary_leave_count',
        'rejoin_blocked',
        'away_since',
        'voice_joined_at',
        'voice_last_seen_at',
        'voice_muted',
        'voice_deafened',
    ];

    protected $casts = [
        'is_bot' => 'boolean',
        'connected' => 'boolean',
        'rejoin_blocked' => 'boolean',
        'away_since' => 'datetime',
        'voice_joined_at' => 'datetime',
        'voice_last_seen_at' => 'datetime',
        'voice_muted' => 'boolean',
        'voice_deafened' => 'boolean',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
