<?php

namespace App\Services\WarqnaPro;

use App\Models\{LuckyWheelSpin, PrizeBox, User};
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LuckyWheelService
{
    public const TOKEN_COST = 100;
    public const MAX_TOKEN_SPINS_PER_DAY = 5;

    public function __construct(
        private readonly PrizeBoxService $prizeBoxes,
        private readonly WalletService $wallet,
    ) {}

    /** @return array<int,array<string,mixed>> */
    public static function segments(): array
    {
        return [
            ['key'=>'ticket_200','label_ar'=>'تذكرة 200','label_en'=>'Competition Ticket 200','icon'=>'🎟️','weight'=>18,'color'=>'#5b21b6','reward'=>['type'=>'ticket','value'=>'200','duration_hours'=>0,'rarity'=>'common','icon'=>'🎟️','label_ar'=>'تذكرة مسابقة 200']],
            ['key'=>'tokens_150','label_ar'=>'150 توكن','label_en'=>'150 Tokens','icon'=>'🪙','weight'=>20,'color'=>'#047857','reward'=>['type'=>'tokens','value'=>'150','duration_hours'=>0,'rarity'=>'common','icon'=>'🪙','label_ar'=>'150 توكن مجاني']],
            ['key'=>'writing_color','label_ar'=>'لون كتابة','label_en'=>'Writing Color','icon'=>'✍️','weight'=>10,'color'=>'#0891b2','reward'=>['type'=>'writing_color','value'=>'#22d3ee','duration_hours'=>24,'rarity'=>'rare','icon'=>'✍️','label_ar'=>'لون كتابة لمدة يوم','store_item_key'=>'lucky_wheel_chat_cyan_v182']],
            ['key'=>'player_color','label_ar'=>'لون لاعب','label_en'=>'Player Color','icon'=>'🎨','weight'=>10,'color'=>'#ca8a04','reward'=>['type'=>'player_color','value'=>'#facc15','duration_hours'=>24,'rarity'=>'rare','icon'=>'🎨','label_ar'=>'لون لاعب لمدة يوم','store_item_key'=>'lucky_wheel_name_gold_v182']],
            ['key'=>'tokens_250','label_ar'=>'250 توكن','label_en'=>'250 Tokens','icon'=>'🪙','weight'=>14,'color'=>'#15803d','reward'=>['type'=>'tokens','value'=>'250','duration_hours'=>0,'rarity'=>'common','icon'=>'🪙','label_ar'=>'250 توكن مجاني']],
            ['key'=>'ticket_500','label_ar'=>'تذكرة 500','label_en'=>'Competition Ticket 500','icon'=>'🎟️','weight'=>10,'color'=>'#7c3aed','reward'=>['type'=>'ticket','value'=>'500','duration_hours'=>0,'rarity'=>'rare','icon'=>'🎟️','label_ar'=>'تذكرة مسابقة 500']],
            ['key'=>'pasha_day','label_ar'=>'يوم باشا','label_en'=>'One Pasha Day','icon'=>'👑','weight'=>5,'color'=>'#dc2626','reward'=>['type'=>'pasha_day','value'=>'1','duration_hours'=>24,'rarity'=>'legendary','icon'=>'👑','label_ar'=>'يوم باشا','store_item_key'=>'lucky_wheel_pasha_day_v182']],
            ['key'=>'royal_box','label_ar'=>'غلاف ملكي','label_en'=>'Royal Cover','icon'=>'🎁','weight'=>4,'color'=>'#be123c','reward'=>['type'=>'profile_cover','value'=>'cover_v02_royal','duration_hours'=>72,'rarity'=>'epic','icon'=>'🖼️','label_ar'=>'غلاف شخصي ملكي لمدة 3 أيام','store_item_key'=>'lucky_wheel_royal_cover_v182']],
            ['key'=>'tokens_500','label_ar'=>'500 توكن','label_en'=>'500 Tokens','icon'=>'💰','weight'=>6,'color'=>'#0f766e','reward'=>['type'=>'tokens','value'=>'500','duration_hours'=>0,'rarity'=>'epic','icon'=>'💰','label_ar'=>'500 توكن مجاني']],
            ['key'=>'ticket_1000','label_ar'=>'تذكرة 1000','label_en'=>'Competition Ticket 1000','icon'=>'🏆','weight'=>3,'color'=>'#92400e','reward'=>['type'=>'ticket','value'=>'1000','duration_hours'=>0,'rarity'=>'legendary','icon'=>'🏆','label_ar'=>'تذكرة مسابقة 1000']],
        ];
    }

    /** @return array<string,mixed> */
    public function center(User $user): array
    {
        $today = now()->toDateString();
        $freeUsed = LuckyWheelSpin::where('user_id',$user->id)->whereDate('spin_date',$today)->where('source','free')->exists();
        $tokenSpins = LuckyWheelSpin::where('user_id',$user->id)->whereDate('spin_date',$today)->where('source','tokens')->count();
        return [
            'segments'=>self::segments(),
            'free_available'=>!$freeUsed,
            'token_cost'=>self::TOKEN_COST,
            'token_spins_today'=>$tokenSpins,
            'token_spins_remaining'=>max(0,self::MAX_TOKEN_SPINS_PER_DAY-$tokenSpins),
            'next_free_at'=>now()->addDay()->startOfDay()->toIso8601String(),
        ];
    }

    /** @return array<string,mixed> */
    public function spin(User $user, string $source='free'): array
    {
        if (!in_array($source, ['free','tokens'], true)) throw new RuntimeException('طريقة التدوير غير صالحة.');

        return DB::transaction(function () use ($user,$source) {
            // Lock the user row first so two simultaneous taps cannot both pass
            // the free-spin or daily token-spin checks before either insert.
            $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $today = now()->toDateString();
            $spins = LuckyWheelSpin::where('user_id',$user->id)->whereDate('spin_date',$today)->lockForUpdate()->get();
            if ($source === 'free' && $spins->contains(fn($spin)=>$spin->source === 'free')) {
                throw new RuntimeException('تم استخدام التدويرة المجانية اليوم.');
            }
            if ($source === 'tokens' && $spins->where('source','tokens')->count() >= self::MAX_TOKEN_SPINS_PER_DAY) {
                throw new RuntimeException('وصلت إلى الحد اليومي للتدوير بالتوكنز.');
            }
            $tokenCost = $source === 'tokens' ? self::TOKEN_COST : 0;
            if ($tokenCost > 0) {
                $this->wallet->debit($user,$tokenCost,'lucky_wheel_spin',['source'=>$source]);
                $this->wallet->creditPrimaryAdminRevenue($user,$tokenCost,'lucky_wheel_income',['source'=>$source]);
            }

            [$index,$segment] = $this->weightedSegment();
            $sourceKey = 'wheel:'.$user->id.':'.now()->format('YmdHisv').':'.bin2hex(random_bytes(3));
            $box = PrizeBox::create([
                'user_id'=>$user->id,
                'box_key'=>'diamond_phoenix',
                'source_type'=>'lucky_wheel',
                'source_key'=>$sourceKey,
                'awarded_date'=>$today,
                'payload'=>['segment_key'=>$segment['key'],'version'=>'V0.3.3'],
            ]);
            $opened = $this->prizeBoxes->open($user,$box,$segment['reward']);
            $spin = LuckyWheelSpin::create([
                'user_id'=>$user->id,'spin_date'=>$today,'source'=>$source,
                'segment_key'=>$segment['key'],'segment_index'=>$index,
                'token_cost'=>$tokenCost,'prize_box_id'=>$box->id,'reward'=>$opened['reward'],
            ]);
            return [
                'spin_id'=>$spin->id,
                'segment_index'=>$index,
                'segment'=>$segment,
                'reward'=>$opened['reward'],
                'inventory'=>$opened['inventory'],
                'tickets'=>$opened['tickets'],
                'wallet'=>$opened['wallet'],
                'profile'=>$opened['profile'],
                'center'=>$this->center($user->fresh()),
            ];
        });
    }

    /** @return array{0:int,1:array<string,mixed>} */
    private function weightedSegment(): array
    {
        $segments = self::segments();
        $total = array_sum(array_map(fn($segment)=>(int)$segment['weight'],$segments));
        $pick = random_int(1,max(1,$total));
        foreach ($segments as $index=>$segment) {
            $pick -= (int)$segment['weight'];
            if ($pick <= 0) return [$index,$segment];
        }
        return [0,$segments[0]];
    }
}
