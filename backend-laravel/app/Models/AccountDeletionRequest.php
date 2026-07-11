<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountDeletionRequest extends Model
{
    protected $fillable = ['user_id','status','requested_at','scheduled_for','cancelled_at','completed_at','reason'];
    protected $casts = ['requested_at'=>'datetime','scheduled_for'=>'datetime','cancelled_at'=>'datetime','completed_at'=>'datetime'];
    public function user(){ return $this->belongsTo(User::class); }
}
