<?php

namespace App\Services\Progression;

use App\Models\{ClubMember, ProgressionEvent, Room, User};
use App\Services\Leveling\XpService;
use App\Services\WarqnaPro\{ChallengeService, PrizeBoxService};
use Illuminate\Support\Facades\DB;

class ProgressionService
{
    public function __construct(private readonly XpService $xpService) {}

    /**
     * Server-authoritative, idempotent progression grant after every round or
     * tournament milestone. Values implement the documented Warqna contract:
     * tournaments outrank normal games, Pasha doubles progression, active boosters stack,
     * sponsored/seasonal competitions use x3, and same-club team play adds x2.
     */
    public function award(User $user, string $eventKey, array $context = []): array
    {
        $existing = ProgressionEvent::where('event_key',$eventKey)->first();
        if ($existing) {
            $meta = is_array($existing->meta) ? $existing->meta : [];
            $prizeBox = null;
            if ($existing->event_type === 'match_complete') {
                $prizeBox = app(PrizeBoxService::class)->awardForCompletedGame(
                    $user,$eventKey,$meta['game'] ?? null,(string)($meta['mode'] ?? $existing->mode ?? 'normal'),(bool)($meta['won'] ?? false)
                );
            }
            return $this->payload($existing, true) + [
                'user_id'=>(int)$user->id,
                'profile'=>$this->profileSnapshot($user),
                'prize_box'=>$prizeBox ? app(PrizeBoxService::class)->boxPayload($prizeBox) : null,
            ];
        }

        return DB::transaction(function () use ($user,$eventKey,$context) {
            $profile = $user->profile()->lockForUpdate()->firstOrCreate([], [
                'display_name'=>$user->username,'country_code'=>'PS','country_name'=>country_name('PS'),
            ]);
            $mode = (string)($context['mode'] ?? 'normal');
            $eventType = (string)($context['event_type'] ?? 'round_complete');
            $won = (bool)($context['won'] ?? false);
            $stage = (string)($context['stage'] ?? 'round');
            $base = match ($eventType) {
                'round_complete' => $won ? 60 : 20,
                'match_complete' => $won ? 140 : 40,
                'tournament_result' => 100,
                default => 20,
            };

            $modeMultiplier = match ($mode) {
                'tournament' => 2.0,
                'sponsored','seasonal' => 3.0,
                'club' => 1.35,
                default => 1.0,
            };
            $pashaMultiplier = ((int)$profile->pasha_days > 0) ? 2.0 : 1.0;
            $boosterActive = !$profile->xp_boost_expires_at || now()->lte($profile->xp_boost_expires_at);
            $boosterMultiplier = $boosterActive ? max(1.0,(float)($profile->xp_boost_multiplier ?? 1)) : 1.0;
            $multiplier = round($modeMultiplier * $pashaMultiplier * $boosterMultiplier, 2);
            $awardedXp = max(1,(int)round($base * $multiplier));
            $roundPoints = max(1,(int)round(($won ? 30 : 8) * $modeMultiplier * $pashaMultiplier * $boosterMultiplier));
            $tournamentPoints = $mode === 'tournament' || $mode === 'sponsored' || $mode === 'seasonal'
                ? $this->tournamentPoints($stage, $modeMultiplier) : 0;
            $clubPoints = $this->clubPoints($user, $won, $mode, (bool)($context['same_club_team'] ?? false));

            $levelResult = $this->xpService->award($user, $awardedXp, 0, $eventType === 'match_complete' && $won, false, false);
            $profile->refresh();
            $profile->round_points = (int)$profile->round_points + $roundPoints;
            $profile->tournament_points = (int)$profile->tournament_points + $tournamentPoints;
            $profile->club_points = (int)$profile->club_points + $clubPoints;
            $profile->save();

            $event = ProgressionEvent::create([
                'user_id'=>$user->id,'room_id'=>$context['room_id'] ?? null,'event_key'=>$eventKey,
                'event_type'=>$eventType,'mode'=>$mode,'base_points'=>$base,'multiplier'=>$multiplier,
                'awarded_xp'=>$levelResult['earned_xp'],'round_points'=>$roundPoints,
                'tournament_points'=>$tournamentPoints,'club_points'=>$clubPoints,
                'meta'=>array_merge($context,['pasha_multiplier'=>$pashaMultiplier,'booster_multiplier'=>$boosterMultiplier]),
            ]);

            $challenges = app(ChallengeService::class);
            if ($eventType === 'match_complete') $challenges->record($user, 'clean_games', 1);
            if ($won) {
                $challenges->record($user, 'wins', 1);
                if (in_array($mode, ['tournament','sponsored','seasonal'], true)) $challenges->record($user, 'ranked_wins', 1);
                if (($context['game'] ?? null) === 'tarneeb') $challenges->record($user, 'tarneeb_big_wins', 1);
            }
            if ($clubPoints > 0) $challenges->record($user, 'club_points', $clubPoints);

            $prizeBox = null;
            if ($eventType === 'match_complete') {
                $prizeBox = app(PrizeBoxService::class)->awardForCompletedGame(
                    $user,
                    $eventKey,
                    isset($context['game']) ? (string)$context['game'] : null,
                    $mode,
                    $won,
                );
            }

            return $this->payload($event, false) + [
                'user_id'=>(int)$user->id,
                'level'=>$levelResult,
                'profile'=>$this->profileSnapshot($user),
                'prize_box'=>$prizeBox ? app(PrizeBoxService::class)->boxPayload($prizeBox) : null,
            ];
        });
    }

    public function tournamentPoints(string $stage, float $multiplier = 2.0): int
    {
        $base = match ($stage) {
            'champion','winner','final_winner' => 1000,
            'runner_up','finalist' => 600,
            'semifinal' => 350,
            'quarterfinal' => 150,
            default => 35,
        };
        // Standard tournaments already use the documented values; only
        // sponsored/seasonal x3 events multiply leaderboard points further.
        return $multiplier >= 3 ? $base * 3 : $base;
    }

    private function clubPoints(User $user, bool $won, string $mode, bool $sameClubTeam): int
    {
        $membership = ClubMember::with('club')->where('user_id',$user->id)->first();
        if (!$membership) return 0;
        $base = $won ? ($mode === 'tournament' ? 50 : 20) : 5;
        $league = strtolower((string)($membership->club?->league_tier ?? 'bronze'));
        $leagueMultiplier = ['bronze'=>1.0,'silver'=>1.15,'gold'=>1.3,'platinum'=>1.5,'diamond'=>1.8,'legendary'=>2.2][$league] ?? 1.0;
        $points = (int)round($base * $leagueMultiplier * ($sameClubTeam ? 2.0 : 1.0));
        $membership->increment('weekly_points',$points);
        $membership->increment('total_points',$points);
        $membership->club?->increment('weekly_points',$points);
        $membership->club?->increment('total_points',$points);
        return $points;
    }

    /** @return array<string,int> */
    private function profileSnapshot(User $user): array
    {
        $profile = $user->profile()->firstOrCreate([], [
            'display_name'=>$user->username,'country_code'=>'PS','country_name'=>country_name('PS'),
        ]);
        $level = (int)($profile->level ?? 1);
        $totalXp = (int)($profile->xp ?? 0);
        $before = 0;
        for ($current = 1; $current < $level; $current++) $before += $this->xpService->requiredXp($current);
        return [
            'level'=>$level,
            'xp'=>$totalXp,
            'xp_progress'=>max(0, $totalXp - $before),
            'xp_next'=>$this->xpService->requiredXp($level),
            'round_points'=>(int)($profile->round_points ?? 0),
            'tournament_points'=>(int)($profile->tournament_points ?? 0),
            'club_points'=>(int)($profile->club_points ?? 0),
        ];
    }

    private function payload(ProgressionEvent $event, bool $duplicate): array
    {
        return ['ok'=>true,'duplicate'=>$duplicate,'event_key'=>$event->event_key,'xp'=>(int)$event->awarded_xp,
            'round_points'=>(int)$event->round_points,'tournament_points'=>(int)$event->tournament_points,
            'club_points'=>(int)$event->club_points,'multiplier'=>(float)$event->multiplier];
    }
}
