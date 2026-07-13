<?php

namespace App\Http\Controllers;

use App\Services\GameEngine\EngineRegistry;
use App\Services\WarqnaPro\ChallengeJourneyService;
use Illuminate\Http\Request;

class MobileChallengeJourneyController extends Controller
{
    public function show(Request $request, ChallengeJourneyService $service)
    {
        return response()->json(['ok'=>true,'run'=>$service->current($request->user())]);
    }

    public function start(Request $request, ChallengeJourneyService $service)
    {
        $data=$request->validate(['game_key'=>'required|string|max:80','stages_total'=>'required|integer|in:10,12,15']);
        abort_unless(EngineRegistry::get($data['game_key']),422,'اللعبة المختارة غير مدعومة.');
        return response()->json(['ok'=>true,'run'=>$service->start($request->user(),$data['game_key'],(int)$data['stages_total'],$this->locale($request))],201);
    }

    public function result(Request $request, ChallengeJourneyService $service)
    {
        $data=$request->validate([
            'won'=>'required|boolean',
            'client_result_id'=>'required|string|min:8|max:120',
            'game_key'=>'required|string|max:80',
        ]);
        return response()->json(['ok'=>true,'run'=>$service->record(
            $request->user(), (bool)$data['won'], (string)$data['client_result_id'], (string)$data['game_key'], $this->locale($request)
        )]);
    }
    private function locale(Request $request): string
    {
        $locale = strtolower(substr((string)($request->header('X-Locale') ?: $request->header('Accept-Language') ?: 'en'), 0, 2));
        return in_array($locale, ['ar','en','de','tr','fr','es'], true) ? $locale : 'en';
    }

}
