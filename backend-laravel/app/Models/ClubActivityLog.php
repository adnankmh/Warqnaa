<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubActivityLog extends Model
{
    protected $fillable = ['club_id','actor_id','event','payload'];
    protected $casts = ['payload'=>'array'];
    public function club(){ return $this->belongsTo(Club::class); }
    public function actor(){ return $this->belongsTo(User::class,'actor_id'); }
}
