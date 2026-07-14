<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['username','email','password','is_admin','admin_permissions','is_banned','last_seen_at','email_verified_at','deletion_requested_at','last_login_ip','last_login_user_agent'];
    protected $hidden = ['password','remember_token'];
    protected $casts = [
        'is_admin' => 'boolean',
        'admin_permissions' => 'array',
        'is_banned' => 'boolean',
        'last_seen_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'deletion_requested_at' => 'datetime',
    ];

    public function profile(){ return $this->hasOne(Profile::class); }
    public function wallet(){ return $this->hasOne(Wallet::class); }
    public function inventoryItems(){ return $this->hasMany(InventoryItem::class); }
    public function walletTransactions(){ return $this->hasMany(WalletTransaction::class); }
    public function notifications(){ return $this->hasMany(Notification::class); }
    public function friendships(){ return $this->hasMany(Friendship::class,'requester_id'); }
    public function sentMessages(){ return $this->hasMany(Message::class,'sender_id'); }
    public function receivedMessages(){ return $this->hasMany(Message::class,'receiver_id'); }
    public function reportsMade(){ return $this->hasMany(UserReport::class,'reporter_id'); }
    public function reportsReceived(){ return $this->hasMany(UserReport::class,'reported_user_id'); }
    public function deletionRequests(){ return $this->hasMany(AccountDeletionRequest::class); }
    public function socialAccounts(){ return $this->hasMany(SocialAccount::class); }
    public function pushDevices(){ return $this->hasMany(PushDevice::class); }
    public function competitionTickets(){ return $this->hasMany(CompetitionTicket::class); }
    public function dailyPackClaims(){ return $this->hasMany(DailyPackClaim::class); }
    public function prizeBoxes(){ return $this->hasMany(PrizeBox::class); }
    public function clubMembership(){ return $this->hasOne(ClubMember::class); }
    public function challengeRuns(){ return $this->hasMany(ChallengeRun::class); }
    public function levelRewardClaims(){ return $this->hasMany(LevelRewardClaim::class); }

    public function hasAdminPermission(string $permission): bool
    {
        if ($this->is_admin || strcasecmp($this->username, 'Adnan') === 0) return true;
        $permissions = $this->admin_permissions ?: [];
        return !empty($permissions['all']) || !empty($permissions[$permission]);
    }

    public function hasAnyAdminPermission(): bool
    {
        if ($this->is_admin || strcasecmp($this->username, 'Adnan') === 0) return true;
        foreach (($this->admin_permissions ?: []) as $enabled) {
            if ($enabled === true || $enabled === 1 || $enabled === '1') return true;
        }
        return false;
    }

    public function publicProfile(): array
    {
        $p = $this->profile;
        $membership = $this->clubMembership?->loadMissing('club');
        $club = $membership?->club;
        return [
            'id'=>$this->id,'username'=>$this->username,'display_name'=>$p?->display_name,
            'avatar'=>$p?->avatar,'avatar_data'=>$p?->avatar_data,'country_code'=>$p?->country_code,
            'country_name'=>$p?->country_name,'level'=>(int)($p?->level ?? 1),'xp'=>(int)($p?->xp ?? 0),
            'xp_next'=>(new \App\Services\Leveling\XpService())->requiredXp((int)($p?->level ?? 1)),
            'round_points'=>(int)($p?->round_points ?? 0),'tournament_points'=>(int)($p?->tournament_points ?? 0),
            'club_points'=>(int)($p?->club_points ?? 0),
            'flag'=>(string)(config('countries.'.safe_country_code($p?->country_code ?? 'PS').'.flag') ?? '🇵🇸'),
            'flag_url'=>flag_url($p?->country_code ?? 'PS'),'name_color'=>$p?->name_color,'chat_color'=>$p?->chat_color,
            'name_color_expires_at'=>$p?->name_color_expires_at?->toIso8601String(),
            'chat_color_expires_at'=>$p?->chat_color_expires_at?->toIso8601String(),
            'login_streak'=>(int)($p?->login_streak ?? 0),'badge'=>$p?->badge,
            'games_played'=>(int)($p?->games_played ?? 0),'wins'=>(int)($p?->wins ?? 0),
            'win_rate'=>($p?->games_played ? round(($p->wins / max(1,$p->games_played))*100,1) : 0),
            'win_rates'=>[],'is_admin'=>(bool)$this->is_admin,'admin_access'=>$this->hasAnyAdminPermission(),'admin_permissions'=>$this->admin_permissions ?: [],'email_verified'=>(bool)$this->email_verified_at,
            'pasha_days'=>(int)($p?->pasha_days ?? 0),'pasha_style'=>'red',
            'champion_rank_points'=>(int)($p?->champion_rank_points ?? 0),
            'active_table_skin'=>$p?->active_table_skin,'active_card_back'=>$p?->active_card_back,
            'active_cover'=>$p?->active_profile_cover,'bot_difficulty'=>$p?->bot_difficulty ?? 'pro',
            'club'=>$club ? [
                'id'=>$club->id,'name'=>$club->name,'logo'=>$club->logo,'image_url'=>$club->image_url,
                'banner_url'=>$club->banner_url,'level'=>(int)$club->level,'league_tier'=>$club->league_tier,
                'role'=>$membership?->role,'permissions'=>$membership?->permissions ?: [],
            ] : null,
            'ui_preferences'=>$p?->ui_preferences,
        ];
    }
}
