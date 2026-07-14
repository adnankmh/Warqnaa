<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    protected $casts = ['settings'=>'array'];
    protected $fillable = [
        'owner_id','name','level','weekly_points','total_points','treasury',
        'capacity','league_tier','description','logo','visibility','image_url','banner_url','settings',
    ];

    public function members(){ return $this->hasMany(ClubMember::class); }
    public function owner(){ return $this->belongsTo(User::class,'owner_id'); }
    public function joinRequests(){ return $this->hasMany(ClubJoinRequest::class); }
    public function announcements(){ return $this->hasMany(ClubAnnouncement::class)->latest(); }
    public function tournaments(){ return $this->hasMany(Tournament::class); }
    public function activityLogs(){ return $this->hasMany(ClubActivityLog::class)->latest(); }
}
