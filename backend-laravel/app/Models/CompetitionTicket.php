<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CompetitionTicket extends Model {
    protected $fillable=['user_id','denomination','quantity','total_used'];
    protected $casts=['denomination'=>'integer','quantity'=>'integer','total_used'=>'integer'];
    public function user(){ return $this->belongsTo(User::class); }
}
