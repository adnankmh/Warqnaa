<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoiceSignal extends Model
{
    protected $fillable = [
        'room_id',
        'sender_id',
        'recipient_id',
        'signal_type',
        'payload',
        'delivered_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'delivered_at' => 'datetime',
    ];
}
