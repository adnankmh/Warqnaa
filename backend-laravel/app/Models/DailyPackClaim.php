<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class DailyPackClaim extends Model
{
    protected $fillable=['user_id','claim_date','reward_type','reward_key','duration_hours','expires_at','payload'];
    protected $casts=['expires_at'=>'datetime','payload'=>'array'];

    protected function claimDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->startOfDay() : null,
            set: fn ($value) => $value ? Carbon::parse($value)->toDateString() : null,
        );
    }

    public function user(){ return $this->belongsTo(User::class); }
}
