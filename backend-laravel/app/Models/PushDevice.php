<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushDevice extends Model
{
    protected $fillable = ['user_id', 'token_hash', 'token', 'platform', 'app_version', 'app_build', 'last_seen_at'];

    protected $hidden = ['token'];

    protected $casts = [
        'token' => 'encrypted',
        'app_build' => 'integer',
        'last_seen_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
