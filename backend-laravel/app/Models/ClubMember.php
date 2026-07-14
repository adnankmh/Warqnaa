<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubMember extends Model
{
    protected $casts=['permissions'=>'array','last_active_at'=>'datetime'];
    protected $fillable=['club_id','user_id','role','permissions','weekly_points','total_points','last_active_at'];

    public function user(){ return $this->belongsTo(User::class); }
    public function club(){ return $this->belongsTo(Club::class); }
}
