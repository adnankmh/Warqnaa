<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = ['username','email','password','is_admin','is_banned','last_seen_at'];
    protected $hidden = ['password','remember_token'];
    protected $casts = [
        'is_admin' => 'boolean',
        'is_banned' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    public function profile(){ return $this->hasOne(Profile::class); }
    public function wallet(){ return $this->hasOne(Wallet::class); }
    public function inventoryItems(){ return $this->hasMany(InventoryItem::class); }
    public function walletTransactions(){ return $this->hasMany(WalletTransaction::class); }
    public function notifications(){ return $this->hasMany(Notification::class); }
    public function friendships(){ return $this->hasMany(Friendship::class,'requester_id'); }
    public function sentMessages(){ return $this->hasMany(Message::class,'sender_id'); }
    public function receivedMessages(){ return $this->hasMany(Message::class,'receiver_id'); }

    public function publicProfile(): array
    {
        $p = $this->profile;
        return [
            'id'=>$this->id,
            'username'=>$this->username,
            'display_name'=>$p?->display_name,
            'avatar'=>$p?->avatar,
            'country_code'=>$p?->country_code,
            'country_name'=>$p?->country_name,
            'level'=>$p?->level,
            'xp'=>$p?->xp,
            'name_color'=>$p?->name_color,
            'badge'=>$p?->badge,
            'games_played'=>(int)($p?->games_played ?? 0),
            'wins'=>(int)($p?->wins ?? 0),
            'win_rate'=>($p?->games_played ? round(($p->wins / max(1,$p->games_played))*100,1) : 0),
            'win_rates'=>[],
            'is_admin'=>(bool)$this->is_admin,
            'is_banned'=>(bool)$this->is_banned,
            'pasha_days'=>(int)($p?->pasha_days ?? 0),
            'chat_color'=>$p?->chat_color,
            'active_table_skin'=>$p?->active_table_skin,
            'active_card_back'=>$p?->active_card_back,
        ];
    }
}
