<?php

namespace App\Services\WarqnaPro;

use App\Models\{CompetitionTicket,Game,Tournament,TournamentEntry,User};
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CompetitionService
{
    public function __construct(private readonly WalletService $wallet) {}

    /** @return array<string,mixed> */
    public function join(User $user, string $key, int $requestedFee): array
    {
        $preset = $this->preset($key, $requestedFee);
        $game = Game::where('active', true)->where('key', $preset['game'])->first() ?: Game::where('active', true)->firstOrFail();
        $tournament = Tournament::firstOrCreate(
            ['key'=>$key],
            [
                'creator_id'=>$user->id,
                'game_id'=>$game->id,
                'stages'=>$preset['rounds'],
                'seats_per_match'=>4,
                'entry_fee'=>$preset['fee'],
                'prize_pool'=>$preset['fee'] * max(8, intdiv($preset['max_players'], 2)),
                'status'=>'open',
                'name'=>['ar'=>$preset['name_ar'],'en'=>$preset['name_en']],
                'description'=>['ar'=>$preset['description_ar'],'en'=>$preset['description_en']],
                'max_players'=>$preset['max_players'],
                'rounds'=>$preset['rounds'],
                'starts_at'=>now()->addMinutes(10),
                'auto_accept'=>true,
                'random_seating'=>true,
                'chat_enabled'=>true,
                'turn_seconds'=>$preset['turn_seconds'],
                'entry_mode'=>'ticket_or_tokens',
                'ad_entry_enabled'=>$preset['fee'] <= 500,
                'featured'=>$preset['featured'],
                'settings'=>['anti_cheat'=>true,'disconnect_grace_seconds'=>45,'entry_fee_locked'=>true],
            ]
        );

        if ($tournament->entries()->where('user_id', $user->id)->exists()) {
            throw new RuntimeException('أنت مسجل في هذه المنافسة مسبقاً.');
        }
        if ($tournament->entries()->count() >= (int)($tournament->max_players ?: 64)) {
            throw new RuntimeException('اكتمل عدد المشاركين في هذه المنافسة.');
        }

        $entryMode = 'tokens';
        $usedTicket = null;
        DB::transaction(function () use ($user, $tournament, &$entryMode, &$usedTicket) {
            $ticket = CompetitionTicket::where('user_id', $user->id)
                ->where('quantity', '>', 0)
                ->where('denomination', '>=', (int)$tournament->entry_fee)
                ->orderBy('denomination')
                ->lockForUpdate()
                ->first();
            if ($ticket) {
                $ticket->decrement('quantity');
                $ticket->increment('total_used');
                $entryMode = 'ticket';
                $usedTicket = (int)$ticket->denomination;
            } else {
                $this->wallet->debit($user, (int)$tournament->entry_fee, 'competition_entry', ['tournament_id'=>$tournament->id,'key'=>$tournament->key]);
            }
            TournamentEntry::create([
                'tournament_id'=>$tournament->id,
                'user_id'=>$user->id,
                'status'=>'registered',
                'entry_mode'=>$entryMode,
                'ticket_denomination'=>$usedTicket,
                'paid_tokens'=>$entryMode === 'tokens' ? (int)$tournament->entry_fee : 0,
                'seed'=>random_int(1, 999999),
            ]);
        });

        return ['tournament'=>$tournament->fresh('game'),'entry_mode'=>$entryMode,'ticket_denomination'=>$usedTicket];
    }

    /** @return array<string,mixed> */
    private function preset(string $key, int $requestedFee): array
    {
        $presets = [
            'champions'=>['name_ar'=>'بطولة الأبطال','name_en'=>'Champions Cup','description_ar'=>'بطولة طرنيب من أربع جولات','description_en'=>'Four-round Tarneeb tournament','fee'=>2000,'max_players'=>64,'rounds'=>4,'turn_seconds'=>10,'game'=>'tarneeb','featured'=>true],
            'weekend'=>['name_ar'=>'كأس نهاية الأسبوع','name_en'=>'Weekend Cup','description_ar'=>'منافسة أسبوعية سريعة','description_en'=>'Fast weekly competition','fee'=>1000,'max_players'=>32,'rounds'=>3,'turn_seconds'=>10,'game'=>'trix','featured'=>true],
            'elite'=>['name_ar'=>'دوري النخبة','name_en'=>'Elite League','description_ar'=>'دوري مصنف للنخبة','description_en'=>'Ranked elite league','fee'=>5000,'max_players'=>128,'rounds'=>4,'turn_seconds'=>10,'game'=>'tarneeb','featured'=>true],
            'clubs_war'=>['name_ar'=>'حرب المجموعات','name_en'=>'Club Wars','description_ar'=>'بطولة فرق المجموعات','description_en'=>'Club team competition','fee'=>10000,'max_players'=>64,'rounds'=>4,'turn_seconds'=>10,'game'=>'tarneeb','featured'=>true],
            'quick'=>['name_ar'=>'المواجهة السريعة','name_en'=>'Quick Clash','description_ar'=>'مواجهة قصيرة بزمن 8 ثوانٍ','description_en'=>'Eight-second quick clash','fee'=>500,'max_players'=>16,'rounds'=>2,'turn_seconds'=>8,'game'=>'basra','featured'=>false],
            'legend'=>['name_ar'=>'كأس الأساطير','name_en'=>'Legends Cup','description_ar'=>'أعلى منافسة موسمية','description_en'=>'Top seasonal tournament','fee'=>20000,'max_players'=>256,'rounds'=>5,'turn_seconds'=>10,'game'=>'tarneeb','featured'=>true],
        ];
        $preset = $presets[$key] ?? $presets['quick'];
        if ($requestedFee > 0 && $requestedFee !== $preset['fee']) throw new RuntimeException('قيمة دخول المنافسة غير مطابقة لإعدادات الخادم.');
        return $preset;
    }
}
