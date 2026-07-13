<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubActivityLog extends Model
{
    protected $fillable = ['club_id','actor_id','subject_user_id','category','action','description','meta'];
    protected $casts = ['meta'=>'array'];

    public function club(){ return $this->belongsTo(Club::class); }
    public function actor(){ return $this->belongsTo(User::class, 'actor_id'); }
    public function subject(){ return $this->belongsTo(User::class, 'subject_user_id'); }
}
