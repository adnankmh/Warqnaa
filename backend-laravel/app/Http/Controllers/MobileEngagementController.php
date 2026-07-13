<?php

namespace App\Http\Controllers;

use App\Models\{AdminDesignerEntity,ChallengeDefinition,ChallengeRun,CompetitionTicket,DailyPackClaim,PrizeBox,Tournament};
use App\Services\WarqnaPro\{ChallengeCampaignService,ChallengeService,CompetitionService,DailyPackService,PrizeBoxService};
use Illuminate\Http\Request;
use RuntimeException;

class MobileEngagementController extends Controller
{
    public function center(Request $request, ChallengeService $challenges, PrizeBoxService $prizeBoxes, ChallengeCampaignService $campaigns)
    {
        $user = $request->user();
        return response()->json([
            'ok'=>true,
            'online_only'=>false,
            'tickets'=>$this->tickets($user->id),
            'daily_pack'=>$this->packStatus($user->id),
            'prize_boxes'=>$prizeBoxes->center($user),
            'inventory'=>$user->inventoryItems()->with('storeItem')->latest()->limit(200)->get(),
            'challenges'=>$challenges->center($user),
            'challenge_road'=>$campaigns->center($user),
            'competitions'=>Tournament::whereIn('status', ['open','running'])->withCount('entries')->orderByDesc('featured')->orderBy('starts_at')->get(),
            'designer'=>AdminDesignerEntity::where('active', true)->orderBy('entity_type')->orderBy('sort_order')->get()->groupBy('entity_type'),
            'champion_rank_points'=>(int)($user->profile?->champion_rank_points ?? 0),
        ]);
    }


    public function prizeBoxes(Request $request, PrizeBoxService $prizeBoxes)
    {
        return response()->json(['ok'=>true, ...$prizeBoxes->center($request->user())]);
    }

    public function openPrizeBox(Request $request, PrizeBox $prizeBox, PrizeBoxService $prizeBoxes)
    {
        try {
            $result = $prizeBoxes->open($request->user(), $prizeBox);
        } catch (RuntimeException $e) {
            return response()->json(['ok'=>false,'message'=>$e->getMessage()], 409);
        }

        return response()->json([
            'ok'=>true,
            'message'=>'تم فتح صندوق الجوائز اليومي وإضافة المكافأة مباشرة.',
            ...$result,
        ]);
    }

    public function openDailyPack(Request $request, DailyPackService $packs)
    {
        try {
            $reward = $packs->open($request->user());
        } catch (RuntimeException $e) {
            return response()->json(['ok'=>false,'message'=>$e->getMessage()], 409);
        }
        return response()->json([
            'ok'=>true,
            'message'=>'تم فتح الحزمة اليومية بنجاح',
            'reward'=>$reward,
            'wallet'=>$this->walletPayload($request->user()->fresh()),
            'tickets'=>$this->tickets($request->user()->id),
            'inventory'=>$request->user()->inventoryItems()->with('storeItem')->latest()->limit(200)->get(),
        ]);
    }

    public function startChallengeRoad(Request $request, ChallengeCampaignService $campaigns)
    {
        $data=$request->validate(['game_key'=>'required|string|max:80','stages'=>'required|integer|in:10,12,15']);
        try{$run=$campaigns->start($request->user(),$data['game_key'],(int)$data['stages']);}
        catch(RuntimeException $e){return response()->json(['ok'=>false,'message'=>$e->getMessage()],422);}
        return response()->json(['ok'=>true,'message'=>'تم بدء طريق التحدي','run'=>$run]);
    }

    public function reportChallengeRoad(Request $request, ChallengeRun $run, ChallengeCampaignService $campaigns)
    {
        $data=$request->validate(['won'=>'required|boolean','room_code'=>'nullable|string|max:40']);
        try{$result=$campaigns->report($request->user(),$run,(bool)$data['won'],$data['room_code']??null);}
        catch(RuntimeException $e){return response()->json(['ok'=>false,'message'=>$e->getMessage()],409);}
        return response()->json(['ok'=>true,'message'=>$data['won']?'تم اجتياز المرحلة وإضافة الجائزة':'خسرت محاولة ويمكنك المحاولة مجدداً',...$result]);
    }

    public function activateChallenge(Request $request, string $challengeKey, ChallengeService $challenges)
    {
        return response()->json(['ok'=>true,'message'=>'تم تفعيل التحدي','challenge'=>$challenges->activate($request->user(), $challengeKey)]);
    }

    public function claimChallenge(Request $request, string $challengeKey, ChallengeService $challenges)
    {
        try {
            $challenge = $challenges->claim($request->user(), $challengeKey);
        } catch (RuntimeException $e) {
            return response()->json(['ok'=>false,'message'=>$e->getMessage()], 409);
        }
        return response()->json(['ok'=>true,'message'=>'تمت إضافة مكافأة التحدي مباشرة','challenge'=>$challenge,'wallet'=>$this->walletPayload($request->user()->fresh())]);
    }

    public function joinCompetition(Request $request, string $competitionKey, CompetitionService $competitions)
    {
        $data = $request->validate(['entry_fee'=>'required|integer|min:0|max:1000000000','entry_mode'=>'nullable|in:auto,ticket,tokens,ad']);
        try {
            $result = $competitions->join($request->user(), $competitionKey, (int)$data['entry_fee']);
        } catch (RuntimeException $e) {
            return response()->json(['ok'=>false,'message'=>$e->getMessage()], 422);
        }
        return response()->json([
            'ok'=>true,
            'message'=>'تم التسجيل في المنافسة',
            ...$result,
            'tickets'=>$this->tickets($request->user()->id),
            'wallet'=>$this->walletPayload($request->user()->fresh()),
            'rank_points'=>(int)($request->user()->profile?->champion_rank_points ?? 0),
        ]);
    }

    /** @return array<string,int> */
    private function tickets(int $userId): array
    {
        return CompetitionTicket::where('user_id', $userId)->pluck('quantity', 'denomination')->map(fn($value)=>(int)$value)->all();
    }

    /** @return array<string,mixed> */
    private function packStatus(int $userId): array
    {
        $claim = DailyPackClaim::where('user_id', $userId)->latest('claim_date')->first();
        return [
            'available'=>!$claim || !$claim->claim_date?->isToday(),
            'last_opened'=>$claim?->claim_date?->toDateString(),
            'last_reward'=>data_get($claim?->payload, 'label_ar'),
            'last_rarity'=>data_get($claim?->payload, 'rarity'),
            'next_available_at'=>$claim?->claim_date?->copy()->addDay()->startOfDay()->toIso8601String(),
            'possible_rewards'=>DailyPackService::catalog(),
        ];
    }

    /** @return array<string,mixed> */
    private function walletPayload($user): array
    {
        $wallet = $user->wallet()->firstOrCreate(['user_id'=>$user->id], ['tokens'=>50,'gems'=>0]);
        return ['tokens'=>(string)$wallet->tokens,'gems'=>(string)$wallet->gems];
    }
}
