<?php
namespace App\Http\Controllers;

use App\Models\{Tournament,TournamentEntry,Game,Room,RoomPlayer};
use App\Services\Wallet\WalletService;
use Illuminate\Http\Request; use RuntimeException;
use Illuminate\Support\Facades\{DB,Schema};

class TournamentController
{
    private array $seatOrder = ['south','north','west','east','south_west','south_east'];

    public function index()
    {
        return view('tournaments.index', [
            'tournaments'=>Tournament::with('creator.profile','game','entries.user.profile')->latest()->get(),
            'games'=>Game::where('active',true)->get(),
        ]);
    }

    public function store(Request $r, WalletService $wallet)
    {
        abort_unless((auth()->user()->profile?->pasha_days ?? 0)>0 || auth()->user()->is_admin,403,'إنشاء المنافسات ميزة لأعضاء الباشا فقط.');
        $data=$r->validate([
            'game_id'=>'required|exists:games,id','stages'=>'required|integer|min:1|max:4','seats_per_match'=>'required|integer|min:2|max:6','entry_fee'=>'required|integer|min:0|max:1000000','prize_pool'=>'required|integer|min:0|max:100000000'
        ]);
        $game=Game::findOrFail($data['game_id']);
        $allowed=$this->allowedSeatsForGame($game);
        if(!in_array((int)$data['seats_per_match'],$allowed,true)) return back()->withErrors(['msg'=>'عدد مقاعد المسابقة غير مناسب لهذه اللعبة. الخيارات الصحيحة: '.implode(' / ',$allowed)]);
        // v142: competition creation is free. Prize pools are administrative/virtual and never debit player wallets.
        $distribution=$this->prizeDistribution((int)$data['prize_pool'], (int)$data['seats_per_match'], (int)$data['stages']);
        $leaderboard=$this->leaderboardPoints((int)$data['stages']);
        $t=Tournament::create($this->safeColumns('tournaments',['creator_id'=>auth()->id()]+$data+['status'=>'open','house_cut_percent'=>10,'prize_distribution'=>$distribution,'leaderboard_points'=>$leaderboard,'bracket'=>['round'=>1,'matches'=>[],'messages'=>['تم إنشاء المسابقة بنظام خروج المغلوب.'],'distribution'=>$distribution,'leaderboard_points'=>$leaderboard]]));
        return back()->with('ok','تم إنشاء المسابقة رقم '.$t->id);
    }

    public function join(Tournament $tournament, WalletService $wallet)
    {
        abort_unless($tournament->status==='open',403,'المسابقة ليست مفتوحة للتسجيل');
        $activeTournament = TournamentEntry::where('user_id',auth()->id())->whereHas('tournament', fn($q)=>$q->whereIn('status',['open','running']))->where('tournament_id','!=',$tournament->id)->first();
        abort_if($activeTournament,403,'أنت مشترك في مسابقة أخرى بالفعل. اخرج أو انتظر انتهاء المسابقة الحالية قبل الاشتراك في مسابقة جديدة.');
        abort_if($tournament->entries()->where('user_id',auth()->id())->exists(),409,'أنت مسجل بالفعل');
        // v142: tournament entry is free; no tokens are deducted during gameplay.
        TournamentEntry::create(['tournament_id'=>$tournament->id,'user_id'=>auth()->id()]);
        $bracket=$tournament->bracket ?: ['round'=>1,'matches'=>[],'messages'=>[]];
        $bracket['messages'][]='سجل اللاعب '.auth()->user()->username.' في المسابقة.';
        $tournament->update(['bracket'=>$bracket]);
        // Prize pool is not funded by player entry deductions in v142.
        $this->tryCreateMatchRoom($tournament->fresh(['entries','game']));
        return back()->with('ok','تم التسجيل في المسابقة');
    }

    public function launch(Tournament $tournament)
    {
        abort_unless($tournament->creator_id===auth()->id() || auth()->user()->is_admin,403);
        $room = $this->tryCreateMatchRoom($tournament->fresh(['entries','game']));
        if(!$room) return back()->withErrors(['msg'=>'لا يوجد عدد كافٍ من المسجلين لبدء مباراة.']);
        return redirect()->route('rooms.show',$room->code)->with('ok','تم فتح غرفة المسابقة');
    }

    public function replay(Tournament $tournament)
    {
        $tournament->load('game','entries.user.profile');
        $match = $tournament->bracket['matches'][0] ?? [];
        $room = !empty($match['room_code']) ? Room::with('players.user.profile','game')->where('code',$match['room_code'])->first() : null;
        $state = $room?->state ?: [];
        $frames = [];
        foreach(($state['log'] ?? []) as $i=>$event){
            $frames[] = [
                'title' => 'الحركة '.($i+1),
                'body' => ($event['player'] ?? $event['system'] ?? 'النظام').' — '.($event['action'] ?? $event['system'] ?? 'تحديث'),
                'payload' => $event['payload'] ?? [],
                'at' => $event['at'] ?? '',
            ];
        }
        if(empty($frames)){
            foreach(($tournament->bracket['messages'] ?? []) as $i=>$msg){ $frames[]=['title'=>'مرحلة '.($i+1),'body'=>$msg,'payload'=>[],'at'=>'']; }
        }
        $finalHands = (($state['phase'] ?? '') === 'finished') ? ($state['hands'] ?? []) : [];
        return view('tournaments.replay', compact('tournament','room','state','frames','finalHands'));
    }


    private function safeColumns(string $table,array $data): array{ try{return array_filter($data,fn($v,$k)=>Schema::hasColumn($table,$k),ARRAY_FILTER_USE_BOTH);}catch(\Throwable $e){return $data;} }
    private function requiredPlayers(int $seats,int $stages): int { return $seats * (2 ** max(0,$stages-1)); }
    private function stageLabel(int $stages): string { return match($stages){1=>'نهائي فقط',2=>'نصف نهائي ثم نهائي',3=>'ربع نهائي ثم نصف نهائي ثم نهائي',4=>'ثمن نهائي ثم ربع نهائي ثم نصف نهائي ثم نهائي',default=>'نهائي'}; }

    private function prizeDistribution(int $basePrize, int $seats, int $stages): array
    {
        $base=max(0,$basePrize);
        $partner=$seats>=4;
        return [
            'mode'=>$partner?'team_split':'winner_takes_all',
            'first_percent'=>$partner?55:100,
            'second_percent'=>$partner?30:0,
            'semifinal_percent'=>$partner?10:0,
            'house_cut_percent'=>10,
            'first_estimate'=>$partner?(int)floor($base*.55):$base,
            'second_estimate'=>$partner?(int)floor($base*.30):0,
            'note'=>$partner?'في ألعاب 4 مقاعد/الشراكة تقسم الجائزة على الفريق أو المراكز حسب نظام المنافسة.':'في ألعاب المقعدين يحصل الفائز على الجائزة كاملة.',
        ];
    }

    private function leaderboardPoints(int $stages): array
    {
        return ['first'=>1000,'second'=>600,'semifinal'=>$stages>=2?350:0,'quarterfinal'=>$stages>=3?150:0,'early'=>50];
    }

    private function allowedSeatsForGame(Game $game): array
    {
        return match($game->key){
            'tarneeb','tarneeb_400','tarneeb_41','trix','trix_partner','baloot','hokm','kout4','basra','estimation','leekha','hearts'=>[4],
            'kout6'=>[6],
            'backgammon'=>[2],
            'pinochle','banakil'=>[2,4],
            'hand','hand_partner','konkan','domino'=>[2,3,4],
            default=>array_values(array_unique(range((int)$game->min_players,min((int)$game->max_players,6))))
        };
    }

    private function tryCreateMatchRoom(Tournament $tournament): ?Room
    {
        $tournament->load('entries','game');
        $bracket = $tournament->bracket ?: ['round'=>1,'matches'=>[],'messages'=>[]];
        if(!empty($bracket['matches'][0]['room_code'])) return Room::where('code',$bracket['matches'][0]['room_code'])->first();
        $needed=$this->requiredPlayers((int)$tournament->seats_per_match,(int)$tournament->stages);
        if($tournament->entries->count() < $needed) return null;

        return DB::transaction(function() use($tournament,$bracket){
            $code=(string)random_int(100000,999999);
            while(Room::where('code',$code)->exists()) $code=(string)random_int(100000,999999);
            $room=Room::create($this->safeColumns('rooms',[
                'code'=>$code,'game_id'=>$tournament->game_id,'owner_id'=>$tournament->creator_id,'visibility'=>'public','password'=>null,'entry_fee'=>0,'min_level'=>1,'target_score'=>$tournament->game->rules['targets'][0] ?? null,'max_players'=>$tournament->seats_per_match,'status'=>'waiting','state'=>['phase'=>'waiting','scores'=>[0,0],'tournament_id'=>$tournament->id,'tournament_stage'=>$this->stageLabel((int)$tournament->stages),'prize_split'=>$tournament->game?->partnership?'تقسم الجائزة على الفريق الفائز':'الفائز يأخذ الجائزة كاملة','prize_distribution'=>$tournament->prize_distribution ?? [],'leaderboard_points'=>$tournament->leaderboard_points ?? [],'log'=>[['system'=>'غرفة مسابقة جاهزة','at'=>now()->toIso8601String()]]]
            ]));
            foreach($tournament->entries->take($tournament->seats_per_match)->values() as $i=>$entry){
                RoomPlayer::create($this->safeColumns('room_players',['room_id'=>$room->id,'user_id'=>$entry->user_id,'seat'=>$this->seatOrder[$i] ?? 'south','is_bot'=>false,'connected'=>true]));
            }
            $bracket['matches'][]=['round'=>1,'room_code'=>$room->code,'players'=>$tournament->entries->take($tournament->seats_per_match)->pluck('user_id')->values()->all(),'status'=>'ready','recording'=>true,'replay_mode'=>'event_log_with_final_hands'];
            $bracket['recording_enabled']=true;
            $bracket['messages'][]='تم إنشاء غرفة المسابقة '.$room->code.' وتفعيل سجل المسابقة وإعادة المشاهدة النصية. بعد انتهاء المباراة يمكن إظهار الحركات والنتائج وأوراق اللاعبين النهائية من سجل الغرفة.';
            $tournament->update(['status'=>'running','bracket'=>$bracket]);
            return $room;
        });
    }
}
