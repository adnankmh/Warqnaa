<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ClubAnnouncement extends Model {
    protected $fillable=['club_id','author_id','title','body','pinned'];
    protected $casts=['pinned'=>'boolean'];
    public function club(){return $this->belongsTo(Club::class);}
    public function author(){return $this->belongsTo(User::class,'author_id');}
}
