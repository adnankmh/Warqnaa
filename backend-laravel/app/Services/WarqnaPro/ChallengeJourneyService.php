<?php

namespace App\Services\WarqnaPro;

use App\Models\{ChallengeRun,CompetitionTicket,PrizeBox,User};
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ChallengeJourneyService
{
    public function __construct(private readonly WalletService $wallet) {}

    /** @return array<string,mixed> */
    public function start(User $user, string $gameKey, int $stages, string $locale = 'en'): array
    {
        if (!in_array($stages, [10,12,15], true)) throw new RuntimeException('عدد المراحل يجب أن يكون 10 أو 12 أو 15.');
        return DB::transaction(function () use ($user, $gameKey, $stages, $locale) {
            ChallengeRun::where('user_id',$user->id)->where('status','active')->update(['status'=>'abandoned','completed_at'=>now()]);
            [$opponentId,$opponentName] = $this->pickOpponent($user, $locale);
            $run = ChallengeRun::create([
                'user_id'=>$user->id,'game_key'=>$gameKey,'stages_total'=>$stages,'current_stage'=>1,
                'attempts_left'=>5,'status'=>'active','current_opponent_user_id'=>$opponentId,
                'current_opponent_name'=>$opponentName,'stage_rewards'=>$this->rewardRoad($stages),
                'claimed_stages'=>[], 'processed_result_ids'=>[],
            ]);
            return $this->payload($run);
        });
    }

    /** @return array<string,mixed>|null */
    public function current(User $user): ?array
    {
        $run = ChallengeRun::where('user_id',$user->id)->where('status','active')->latest()->first();
        return $run ? $this->payload($run) : null;
    }

    /** @return array<string,mixed> */
    public function record(User $user, bool $won, string $clientResultId, string $gameKey, string $locale = 'en'): array
    {
        return DB::transaction(function () use ($user,$won,$clientResultId,$gameKey,$locale) {
            $run = ChallengeRun::where('user_id',$user->id)->where('status','active')->lockForUpdate()->latest()->first();
            if (!$run) throw new RuntimeException('لا يوجد مسار تحدٍ فعال.');
            if (!hash_equals((string)$run->game_key, $gameKey)) throw new RuntimeException('نتيجة اللعبة لا تطابق نوع لعبة مسار التحدي.');
            $processed = array_values(array_filter(array_map('strval', (array)($run->processed_result_ids ?? []))));
            if ($clientResultId !== '' && in_array($clientResultId, $processed, true)) {
                return $this->payload($run) + ['duplicate'=>true, 'reward_granted'=>null];
            }

            $claimed = array_map('intval', (array)($run->claimed_stages ?? []));
            $reward = null;
            if ($won) {
                $stage = (int)$run->current_stage;
                if (!in_array($stage,$claimed,true)) {
                    $rewards = (array) ($run->stage_rewards ?? []);
                    $reward = $this->applyReward($user, $stage, (array) ($rewards[$stage - 1] ?? []), (int)$run->id);
                    $claimed[] = $stage;
                }
                if ($stage >= (int)$run->stages_total) {
                    $run->status = 'completed';
                    $run->completed_at = now();
                } else {
                    $run->current_stage = $stage + 1;
                    [$opponentId,$opponentName] = $this->pickOpponent($user, $locale);
                    $run->current_opponent_user_id = $opponentId;
                    $run->current_opponent_name = $opponentName;
                }
            } else {
                $run->attempts_left = max(0, (int)$run->attempts_left - 1);
                if ((int)$run->attempts_left <= 0) {
                    $run->status = 'failed';
                    $run->completed_at = now();
                } else {
                    [$opponentId,$opponentName] = $this->pickOpponent($user, $locale);
                    $run->current_opponent_user_id = $opponentId;
                    $run->current_opponent_name = $opponentName;
                }
            }
            $run->claimed_stages = array_values(array_unique($claimed));
            $run->last_result = $won ? 'win' : 'loss';
            $run->last_client_result_id = $clientResultId ?: null;
            if ($clientResultId !== '') {
                $processed[] = $clientResultId;
                $run->processed_result_ids = array_slice(array_values(array_unique($processed)), -50);
            }
            $run->save();
            return $this->payload($run) + ['reward_granted'=>$reward];
        });
    }

    /** @return array<int,array<string,mixed>> */
    public function rewardRoad(int $stages): array
    {
        $road=[];
        for ($stage=1; $stage<=$stages; $stage++) {
            $road[] = match (true) {
                $stage === $stages => ['type'=>'pasha_days','amount'=>3,'icon'=>'👑','label_ar'=>'3 أيام باشا'],
                $stage % 7 === 0 => ['type'=>'table_days','amount'=>5,'value'=>'table_v025_challenge','icon'=>'🎴','label_ar'=>'طاولة 5 أيام'],
                $stage % 6 === 0 => ['type'=>'prize_box','amount'=>1,'value'=>'royal_amethyst','icon'=>'📦','label_ar'=>'صندوق ملكي'],
                $stage % 5 === 0 => ['type'=>'ticket','amount'=>1,'value'=>200,'icon'=>'🎟️','label_ar'=>'تذكرة مسابقة 200'],
                $stage % 4 === 0 => ['type'=>'booster_hours','amount'=>4,'icon'=>'⚡','label_ar'=>'مسرّع 4 ساعات'],
                $stage % 3 === 0 => ['type'=>'chat_color_days','amount'=>3,'value'=>'#a78bfa','icon'=>'✍️','label_ar'=>'لون كتابة 3 أيام'],
                $stage % 2 === 0 => ['type'=>'tokens','amount'=>min(1000,150+$stage*50),'icon'=>'🪙','label_ar'=>min(1000,150+$stage*50).' توكن'],
                default => ['type'=>'name_color_days','amount'=>2,'value'=>'#facc15','icon'=>'🎨','label_ar'=>'لون لاعب يومان'],
            };
        }
        return $road;
    }

    /** @return array<string,mixed> */
    private function applyReward(User $user, int $stage, array $reward, int $runId): array
    {
        $type=(string)($reward['type'] ?? 'tokens');
        $amount=(int)($reward['amount'] ?? 1);
        $profile=$user->profile()->lockForUpdate()->firstOrCreate([],['display_name'=>$user->username,'country_code'=>'PS','country_name'=>country_name('PS')]);
        if ($type === 'tokens') $this->wallet->credit($user,min(1000,max(1,$amount)),'challenge_stage',['run_id'=>$runId,'stage'=>$stage]);
        elseif ($type === 'pasha_days') $profile->pasha_days=(int)$profile->pasha_days+$amount;
        elseif ($type === 'ticket') {
            $ticket=CompetitionTicket::firstOrCreate(['user_id'=>$user->id,'denomination'=>200],['quantity'=>0,'total_used'=>0]);
            $ticket->increment('quantity',$amount);
        } elseif ($type === 'booster_hours') {
            $base=$profile->xp_boost_expires_at && now()->lt($profile->xp_boost_expires_at) ? $profile->xp_boost_expires_at : now();
            $profile->xp_boost_multiplier=max(2,(float)($profile->xp_boost_multiplier ?? 1));
            $profile->xp_boost_expires_at=$base->copy()->addHours($amount);
        } elseif ($type === 'chat_color_days') {
            $profile->chat_color=(string)$reward['value']; $profile->text_color=(string)$reward['value']; $profile->chat_color_expires_at=now()->addDays($amount);
        } elseif ($type === 'name_color_days') {
            $profile->name_color=(string)$reward['value']; $profile->name_color_expires_at=now()->addDays($amount);
        } elseif ($type === 'table_days') {
            $prefs=is_array($profile->ui_preferences)?$profile->ui_preferences:[];
            $prefs['temporary_table']=['key'=>(string)$reward['value'],'expires_at'=>now()->addDays($amount)->toIso8601String()];
            $profile->ui_preferences=$prefs;
        } elseif ($type === 'prize_box') {
            PrizeBox::firstOrCreate(['user_id'=>$user->id,'source_key'=>'challenge:'.$runId.':'.$stage],[
                'box_key'=>(string)$reward['value'],'source_type'=>'challenge_stage','awarded_date'=>now()->toDateString(),
                'payload'=>['run_id'=>$runId,'stage'=>$stage,'version'=>'V0.2.5'],
            ]);
        }
        $profile->save();
        return $reward + ['stage'=>$stage];
    }

    /** @return array{0:int|null,1:string} */
    private function pickOpponent(User $user, string $locale = 'en'): array
    {
        $candidate=User::with('profile')
            ->where('id','!=',$user->id)
            ->where('is_banned',false)
            ->where('last_seen_at','>=',now()->subMinutes(3))
            ->inRandomOrder()
            ->first();
        if (!$candidate) {
            $candidate=User::with('profile')->where('id','!=',$user->id)->where('is_banned',false)->inRandomOrder()->first();
        }
        if ($candidate) return [(int)$candidate->id,(string)($candidate->profile?->display_name ?: $candidate->username)];
        $bots = strtolower(substr($locale, 0, 2)) === 'ar'
            ? ['عدنان','بيان','كنان','جميل','رعد','عاصم','معتصم','حسام','جنان','حور','جنات','آلاء','أفنان','شهد','حلا','شذى','قمر']
            : ['Adnan','Bayan','Kenan','Jameel','Raad','Asem','Moatasem','Hossam','Janan','Hoor','Jannat','Alaa','Afnan','Shahd','Hala','Shatha','Qamar'];
        return [null,$bots[array_rand($bots)]];
    }

    /** @return array<string,mixed> */
    private function payload(ChallengeRun $run): array
    {
        return [
            'id'=>(int)$run->id,'game_key'=>$run->game_key,'stages_total'=>(int)$run->stages_total,
            'current_stage'=>(int)$run->current_stage,'attempts_left'=>(int)$run->attempts_left,
            'status'=>$run->status,'opponent'=>['user_id'=>$run->current_opponent_user_id,'name'=>$run->current_opponent_name],
            'stage_rewards'=>$run->stage_rewards ?? [],'claimed_stages'=>array_map('intval',(array)($run->claimed_stages ?? [])),
            'last_result'=>$run->last_result,'completed_at'=>$run->completed_at?->toIso8601String(),
        ];
    }
}
