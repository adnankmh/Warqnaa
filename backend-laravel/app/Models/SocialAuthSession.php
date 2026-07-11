<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SocialAuthSession extends Model {
    protected $fillable=['state','provider','status','user_id','one_time_token','error','expires_at'];
    protected $casts=['expires_at'=>'datetime','one_time_token'=>'encrypted'];
    public function user(){return $this->belongsTo(User::class);}
}
