<?php

namespace App\Services\Leveling;

use App\Models\{CompetitionTicket,InventoryItem,LevelRewardClaim,StoreItem,User};
use App\Services\WarqnaPro\PrizeBoxService;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;

class LevelUpRewardService
{
    public function __construct(private readonly WalletService $wallet) {}

    /** @return array<string,mixed> */
    public function grant(User $user, int $level): array
    {
        $existing=LevelRewardClaim::where('user_id',$user->id)->where('level',$level)->first();
        if($existing) return $existing->payload ?? ['level'=>$level,'already_claimed'=>true];

        return DB::transaction(function() use($user,$level){
            $locked=LevelRewardClaim::where('user_id',$user->id)->where('level',$level)->lockForUpdate()->first();
            if($locked) return $locked->payload ?? ['level'=>$level,'already_claimed'=>true];
            $reward=$this->definition($level);
            $profile=$user->profile()->lockForUpdate()->first();
            $expiresAt=null;
            switch($reward['type']){
                case 'pasha_days':
                    if($profile){$profile->pasha_days=(int)$profile->pasha_days+(int)$reward['amount'];$profile->pasha_style='red';$profile->save();}
                    break;
                case 'prize_box':
                    app(PrizeBoxService::class)->awardBonus($user,'level:'.$level,'level_up');
                    break;
                case 'ticket':
                    $ticket=CompetitionTicket::firstOrCreate(['user_id'=>$user->id,'denomination'=>(int)$reward['amount']],['quantity'=>0]);
                    $ticket->increment('quantity');
                    break;
                case 'name_color':
                case 'writing_color':
                    $hours=(int)($reward['hours'] ?? 24);$expiresAt=now()->addHours($hours);
                    $category=$reward['type']==='name_color'?'name_color':'text_color';
                    $value=$reward['type']==='name_color'?'#facc15':'#22d3ee';
                    $item=StoreItem::firstOrCreate(['key'=>'level_'.$level.'_'.$reward['type'].'_v03'],[
                        'name'=>['ar'=>'مكافأة مستوى '.$level,'en'=>'Level '.$level.' reward'],'category'=>$category,'price'=>0,
                        'duration_days'=>max(1,(int)ceil($hours/24)),'payload'=>['source'=>'level_up_v03','value'=>$value],'active'=>true,
                    ]);
                    InventoryItem::create(['user_id'=>$user->id,'store_item_id'=>$item->id,'active'=>true,'activated_at'=>now(),'expires_at'=>$expiresAt]);
                    if($profile){
                        if($reward['type']==='name_color'){$profile->name_color=$value;$profile->name_color_expires_at=$expiresAt;}
                        else{$profile->chat_color=$value;$profile->text_color=$value;$profile->chat_color_expires_at=$expiresAt;}
                        $profile->save();
                    }
                    break;
                default:
                    $this->wallet->credit($user,(int)$reward['amount'],'level_up_reward',['level'=>$level]);
            }
            LevelRewardClaim::create([
                'user_id'=>$user->id,'level'=>$level,'reward_type'=>$reward['type'],'reward_key'=>$reward['key'] ?? null,
                'amount'=>(int)($reward['amount'] ?? 0),'expires_at'=>$expiresAt,'payload'=>$reward,
            ]);
            return $reward;
        });
    }

    /** @return array<string,mixed> */
    public function definition(int $level): array
    {
        if($level%25===0) return ['level'=>$level,'type'=>'pasha_days','amount'=>3,'icon'=>'👑','label_ar'=>'3 أيام باشا','label_en'=>'3 Pasha days'];
        if($level%10===0) return ['level'=>$level,'type'=>'prize_box','amount'=>1,'icon'=>'🎁','label_ar'=>'صندوق جوائز إضافي','label_en'=>'Bonus prize box'];
        if($level%5===0) return ['level'=>$level,'type'=>'ticket','amount'=>200,'icon'=>'🎟️','label_ar'=>'تذكرة مسابقة 200','label_en'=>'200 competition ticket'];
        if($level%4===0) return ['level'=>$level,'type'=>'name_color','amount'=>48,'hours'=>48,'icon'=>'🎨','label_ar'=>'لون لاعب لمدة يومين','label_en'=>'Player color for 2 days'];
        if($level%3===0) return ['level'=>$level,'type'=>'writing_color','amount'=>24,'hours'=>24,'icon'=>'✍️','label_ar'=>'لون كتابة لمدة يوم','label_en'=>'Writing color for 1 day'];
        $tokens=min(1000,50*max(1,(int)ceil($level/2)));
        return ['level'=>$level,'type'=>'tokens','amount'=>$tokens,'icon'=>'🪙','label_ar'=>$tokens.' توكن','label_en'=>$tokens.' tokens'];
    }
}
