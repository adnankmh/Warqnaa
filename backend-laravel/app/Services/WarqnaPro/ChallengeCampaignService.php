<?php

namespace App\Services\WarqnaPro;

use App\Models\{ChallengeCampaign,ChallengeRun,CompetitionTicket,InventoryItem,StoreItem,User};
use App\Services\Wallet\WalletService;
use App\Services\GameEngine\EngineRegistry;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ChallengeCampaignService
{
    public const STAGE_OPTIONS = [10, 12, 15];
    public const STARTING_LIVES = 5;

    public function __construct(private readonly WalletService $wallet) {}

    /** @return array<int,array<string,mixed>> */
    public static function rewardRoad(int $stages): array
    {
        $road=[];
        for($stage=1;$stage<=$stages;$stage++) {
            $road[] = match (true) {
                $stage === $stages => ['stage'=>$stage,'type'=>'pasha_days','amount'=>3,'label_ar'=>'3 أيام باشا','label_en'=>'3 Pasha days','icon'=>'👑'],
                $stage % 10 === 0 => ['stage'=>$stage,'type'=>'prize_box','amount'=>1,'label_ar'=>'صندوق جوائز أسطوري','label_en'=>'Legendary prize box','icon'=>'🎁'],
                $stage % 7 === 0 => ['stage'=>$stage,'type'=>'table_days','amount'=>3,'label_ar'=>'طاولة أسطورية 3 أيام','label_en'=>'Legendary table for 3 days','icon'=>'🎴'],
                $stage % 5 === 0 => ['stage'=>$stage,'type'=>'ticket','amount'=>200,'label_ar'=>'تذكرة مسابقة 200','label_en'=>'200 competition ticket','icon'=>'🎟️'],
                $stage % 4 === 0 => ['stage'=>$stage,'type'=>'name_color','amount'=>48,'label_ar'=>'لون لاعب لمدة يومين','label_en'=>'Player color for 2 days','icon'=>'🎨'],
                $stage % 3 === 0 => ['stage'=>$stage,'type'=>'writing_color','amount'=>24,'label_ar'=>'لون كتابة لمدة يوم','label_en'=>'Writing color for 1 day','icon'=>'✍️'],
                default => ['stage'=>$stage,'type'=>'tokens','amount'=>min(1000, 50 * max(1,$stage)),'label_ar'=>(min(1000,50*max(1,$stage))).' توكن','label_en'=>(min(1000,50*max(1,$stage))).' tokens','icon'=>'🪙'],
            };
        }
        return $road;
    }

    public function center(User $user): array
    {
        $active = ChallengeRun::with(['campaign','opponent.profile'])
            ->where('user_id',$user->id)->where('status','active')->latest()->first();
        $configured=(array)config('warqna.challenge_games', array_keys(EngineRegistry::all()));
        $supported=array_values(array_filter($configured, fn($key)=>is_string($key) && EngineRegistry::get($key)!==null));
        return [
            'stage_options'=>self::STAGE_OPTIONS,
            'starting_lives'=>self::STARTING_LIVES,
            'active_run'=>$active ? $this->payload($active) : null,
            'supported_games'=>$supported,
        ];
    }

    public function start(User $user, string $gameKey, int $stages): array
    {
        if (!in_array($stages,self::STAGE_OPTIONS,true)) throw new RuntimeException('عدد المراحل غير مدعوم.');
        $gameKey=trim($gameKey);
        if($gameKey==='') throw new RuntimeException('اختر نوع اللعبة أولاً.');
        if(EngineRegistry::get($gameKey)===null) throw new RuntimeException('نوع اللعبة المحدد غير مدعوم في طريق التحدي.');
        return DB::transaction(function() use($user,$gameKey,$stages){
            ChallengeRun::where('user_id',$user->id)->where('status','active')->update(['status'=>'abandoned']);
            $campaign=ChallengeCampaign::firstOrCreate(
                ['key'=>'road_'.$stages],
                ['name'=>['ar'=>'طريق الأبطال '.$stages,'en'=>'Champions Road '.$stages],'stage_count'=>$stages,'starting_lives'=>self::STARTING_LIVES,'active'=>true,'rewards'=>self::rewardRoad($stages)]
            );
            $run=ChallengeRun::create([
                'user_id'=>$user->id,'challenge_campaign_id'=>$campaign->id,'game_key'=>$gameKey,
                'stage'=>1,'lives'=>self::STARTING_LIVES,'status'=>'active','claimed_stages'=>[],'history'=>[],
                'opponent_user_id'=>$this->randomOpponentId($user),
            ]);
            return $this->payload($run->fresh(['campaign','opponent.profile']));
        });
    }

    public function report(User $user, ChallengeRun $run, bool $won, ?string $roomCode=null): array
    {
        if((int)$run->user_id !== (int)$user->id) throw new RuntimeException('هذا التحدي لا يخص حسابك.');
        if($run->status!=='active') throw new RuntimeException('هذا التحدي غير نشط.');
        return DB::transaction(function() use($user,$run,$won,$roomCode){
            $run=ChallengeRun::whereKey($run->id)->lockForUpdate()->firstOrFail();
            $history=$run->history ?? [];
            $roomCode=trim((string)$roomCode);
            if($roomCode!=='' && collect($history)->contains(fn($entry)=>(string)($entry['room_code'] ?? '')===$roomCode)) {
                throw new RuntimeException('تم احتساب نتيجة هذه المباراة مسبقاً.');
            }
            $current=max(1,(int)$run->stage);
            $history[]=['stage'=>$current,'won'=>$won,'room_code'=>$roomCode!==''?$roomCode:null,'played_at'=>now()->toIso8601String()];
            $reward=null;
            if($won){
                $run->wins=(int)$run->wins+1;
                $reward=$this->grantStageReward($user,$run,$current);
                if($current >= (int)$run->campaign->stage_count){
                    $run->status='completed'; $run->completed_at=now();
                } else {
                    $run->stage=$current+1;
                }
            } else {
                $run->losses=(int)$run->losses+1;
                $run->lives=max(0,(int)$run->lives-1);
                if((int)$run->lives<=0){$run->status='failed';$run->completed_at=now();}
            }
            $run->history=$history;
            $run->opponent_user_id=$run->status==='active' ? $this->randomOpponentId($user) : null;
            $run->save();
            return ['run'=>$this->payload($run->fresh(['campaign','opponent.profile'])),'reward'=>$reward,'wallet'=>$user->wallet()->firstOrCreate(['user_id'=>$user->id],['tokens'=>50,'gems'=>0])];
        });
    }

    private function grantStageReward(User $user, ChallengeRun $run, int $stage): array
    {
        $claimed=array_map('intval',$run->claimed_stages ?? []);
        if(in_array($stage,$claimed,true)) return ['already_claimed'=>true];
        $road=$run->campaign->rewards ?: self::rewardRoad((int)$run->campaign->stage_count);
        $reward=collect($road)->firstWhere('stage',$stage) ?: ['stage'=>$stage,'type'=>'tokens','amount'=>50,'label_ar'=>'50 توكن','label_en'=>'50 tokens','icon'=>'🪙'];
        $profile=$user->profile()->lockForUpdate()->first();
        $amount=(int)($reward['amount'] ?? 0);
        switch($reward['type']){
            case 'tokens': $this->wallet->credit($user,$amount,'challenge_stage_reward',['run_id'=>$run->id,'stage'=>$stage]); break;
            case 'ticket': $ticket=CompetitionTicket::firstOrCreate(['user_id'=>$user->id,'denomination'=>200],['quantity'=>0]); $ticket->increment('quantity'); break;
            case 'pasha_days': if($profile){$profile->pasha_days=(int)$profile->pasha_days+$amount;$profile->pasha_style='red';$profile->save();} break;
            case 'prize_box': app(PrizeBoxService::class)->awardBonus($user,'challenge:'.$run->id.':'.$stage,$run->game_key); break;
            case 'name_color': $this->grantTimedStore($user,'challenge_name_gold_v03','name_color','#facc15',$amount); if($profile){$profile->name_color='#facc15';$profile->name_color_expires_at=now()->addHours($amount);$profile->save();} break;
            case 'writing_color': $this->grantTimedStore($user,'challenge_chat_cyan_v03','text_color','#22d3ee',$amount); if($profile){$profile->chat_color='#22d3ee';$profile->text_color='#22d3ee';$profile->chat_color_expires_at=now()->addHours($amount);$profile->save();} break;
            case 'table_days': $hours=$amount*24; $this->grantTimedStore($user,'challenge_table_legend_v03','table','table_reference_20',$hours); if($profile){$profile->active_table_skin='table_reference_20';$profile->save();} break;
        }
        $claimed[]=$stage;$run->claimed_stages=array_values(array_unique($claimed));$run->save();
        return $reward;
    }

    private function grantTimedStore(User $user,string $key,string $category,string $value,int $hours): void
    {
        $item=StoreItem::firstOrCreate(['key'=>$key],['name'=>['ar'=>'مكافأة تحدي مؤقتة','en'=>'Timed challenge reward'],'category'=>$category,'price'=>0,'duration_days'=>max(1,(int)ceil($hours/24)),'payload'=>['source'=>'challenge_road_v03','value'=>$value],'active'=>true]);
        InventoryItem::create(['user_id'=>$user->id,'store_item_id'=>$item->id,'active'=>true,'activated_at'=>now(),'expires_at'=>now()->addHours(max(1,$hours))]);
    }

    private function randomOpponentId(User $user): ?int
    {
        return User::whereKeyNot($user->id)->where('is_banned',false)->whereHas('profile')->inRandomOrder()->value('id');
    }

    private function payload(ChallengeRun $run): array
    {
        $campaign=$run->campaign;
        return [
            'id'=>$run->id,'game_key'=>$run->game_key,'stage'=>(int)$run->stage,'stage_count'=>(int)$campaign->stage_count,
            'lives'=>(int)$run->lives,'starting_lives'=>(int)$campaign->starting_lives,'wins'=>(int)$run->wins,'losses'=>(int)$run->losses,
            'status'=>$run->status,'reward_road'=>$campaign->rewards ?: self::rewardRoad((int)$campaign->stage_count),
            'claimed_stages'=>$run->claimed_stages ?? [],
            'opponent'=>$run->opponent ? $run->opponent->publicProfile() : null,
        ];
    }
}
