<?php
namespace App\Services\GameEngine;

require_once __DIR__.'/TarneebStandalone/TarneebEngine.php';

use App\Services\GameEngine\TarneebStandalone\TarneebEngine;
use App\Services\GameEngine\TarneebStandalone\TarneebException;
use App\Services\WarqnaPro\PlayActionNormalizer;

class TarneebRules implements GameRuleContract
{
    private TarneebEngine $engine;
    private PlayActionNormalizer $normalizer;

    public function __construct()
    {
        $this->engine = new TarneebEngine();
        $this->normalizer = new PlayActionNormalizer();
    }

    public function initialState(array $players, array $options=[]): array
    {
        $players=array_values(array_slice($players,0,4));
        while(count($players)<4) $players[]='bot:tarneeb_'.count($players);
        $target=(int)($options['target'] ?? 41);
        if(!in_array($target,[31,41,61],true)) $target=61;
        $standalonePlayers=[];
        foreach($players as $i=>$p){
            $standalonePlayers[]=['id'=>$p,'name'=>$this->shortName($p),'bot'=>str_starts_with((string)$p,'bot:')];
        }
        $state=$this->engine->newGameWithTarget($standalonePlayers,$target,random_int(1,PHP_INT_MAX),[
            'turnSeconds'=>max(5,min(10,(int)($options['turn_seconds'] ?? 7))),
            'targetScore'=>$target,
            'redealOnAllPass'=>true,
            'sortHands'=>true,
            'allowBotForAway'=>true,
            'maxMissedTurnsBeforeAway'=>3,
        ]);
        $state=$this->engine->autoPlayBotsAndAway($state,40);
        return $this->fromStandalone($state,$players,[
            'messages'=>['طرنيب v132: محرك الطرنيب المرفق مدمج الآن. الطلب 7-13، Pass، اختيار الطرنيب، اتباع النوع، وحساب اللمّات والنقاط من السيرفر.'],
        ]);
    }

    public function validate(array $state, string $playerId, string $action, array $payload): bool
    {
        try{
            $s=$this->toStandalone($state);
            if(!$s) return false;
            $action=$this->normalizeAction($action);
            if($action==='next_round') { $this->engine->nextRound($s); return true; }
            if(($state['turn'] ?? null)!==$playerId) return false;
            if($action==='pass') { $this->engine->bid($s,$playerId,null); return true; }
            if($action==='bid') { $this->engine->bid($s,$playerId,(int)($payload['value'] ?? 0)); return true; }
            if($action==='choose_trump') { $this->engine->chooseTrump($s,$playerId,$this->toShortSuit((string)($payload['suit'] ?? ''))); return true; }
            if($action==='play_card') {
                $hand=$state['hands'][$playerId] ?? [];
                $card=$this->canonicalCard($this->buildCardCandidateFromPayload($payload),$hand);
                if(!$card) return false;
                $this->engine->playCard($s,$playerId,$this->toShortCard($card));
                return true;
            }
            return false;
        }catch(\Throwable $e){ return false; }
    }

    public function apply(array $state, string $playerId, string $action, array $payload): array
    {
        $oldMessages=$state['messages'] ?? [];
        try{
            $s=$this->toStandalone($state);
            if(!$s){ $state['last_error_message']='تعذر قراءة حالة الطرنيب.'; return $state; }
            $action=$this->normalizeAction($action);
            if($action==='next_round') $s=$this->engine->nextRound($s);
            elseif($action==='pass') $s=$this->engine->bid($s,$playerId,null);
            elseif($action==='bid') $s=$this->engine->bid($s,$playerId,(int)($payload['value'] ?? 0));
            elseif($action==='choose_trump') $s=$this->engine->chooseTrump($s,$playerId,$this->toShortSuit((string)($payload['suit'] ?? '')));
            elseif($action==='play_card'){
                $hand=$state['hands'][$playerId] ?? [];
                $card=$this->canonicalCard($this->buildCardCandidateFromPayload($payload),$hand);
                if(!$card) throw new \RuntimeException('هذه الورقة ليست في يدك.');
                $s=$this->engine->playCard($s,$playerId,$this->toShortCard($card));
            }else{
                throw new \RuntimeException('حركة غير مدعومة في الطرنيب.');
            }
            $s=$this->engine->autoPlayBotsAndAway($s,40);
            $next=$this->fromStandalone($s,array_values($state['players'] ?? []));
            $next['messages']=array_values(array_slice(array_merge($oldMessages,$this->messagesFromEvents($s)),-40));
            unset($next['last_error'],$next['last_error_message']);
            return $next;
        }catch(TarneebException $e){
            $state['last_error']='tarneeb_'.$e->codeKey;
            $state['last_error_message']=$e->getMessage();
            $state['messages'][]=$e->getMessage();
            return $state;
        }catch(\Throwable $e){
            $state['last_error']='tarneeb_error';
            $state['last_error_message']=$e->getMessage();
            $state['messages'][]='حركة غير صحيحة في الطرنيب: '.$e->getMessage();
            return $state;
        }
    }

    public function onTurnTimeout(array $state): array
    {
        try{
            $s=$this->toStandalone($state);
            if(!$s) return $state;
            $s=$this->engine->onTurnTimeout($s);
            $s=$this->engine->autoPlayBotsAndAway($s,40);
            $next=$this->fromStandalone($s,array_values($state['players'] ?? []));
            $next['messages']=array_values(array_slice(array_merge($state['messages'] ?? [],['⏱️ انتهى وقت الدور، تم تنفيذ حركة تلقائية في الطرنيب.'],$this->messagesFromEvents($s)),-40));
            return $next;
        }catch(\Throwable $e){ return $state; }
    }

    private function fromStandalone(array $s,array $fallbackPlayers=[],array $extra=[]): array
    {
        $players=[];
        foreach(($s['players'] ?? []) as $p) $players[]=(string)$p['id'];
        if(!$players) $players=$fallbackPlayers;
        $hands=[];
        foreach(($s['hands'] ?? []) as $seat=>$cards){
            $pid=$players[(int)$seat] ?? null;
            if($pid) $hands[$pid]=$this->longCards((array)$cards);
        }
        $turn=$players[(int)($s['currentSeat'] ?? 0)] ?? ($players[0] ?? null);
        $teamA=array_values(array_filter([$players[0] ?? null,$players[2] ?? null]));
        $teamB=array_values(array_filter([$players[1] ?? null,$players[3] ?? null]));
        $bid=null;
        if(!empty($s['bid']['amount']) && isset($s['bid']['seat'])){
            $bp=$players[(int)$s['bid']['seat']] ?? null;
            $bid=['player'=>$bp,'value'=>(int)$s['bid']['amount'],'team'=>((int)($s['bid']['team'] ?? 0)===0?'teamA':'teamB')];
        }
        $passed=[];
        foreach(($s['bid']['passed'] ?? []) as $seat=>$v){
            if($v && isset($players[(int)$seat])) $passed[$players[(int)$seat]]=true;
        }
        $trick=[];
        foreach(($s['trick'] ?? []) as $play){
            $pid=$players[(int)($play['seat'] ?? -1)] ?? null;
            if($pid) $trick[$pid]=$this->toLongCard((string)($play['card'] ?? ''));
        }
        $last=[];
        $completed=$s['completedTricks'] ?? [];
        if(!empty($completed)){
            $cards=end($completed)['cards'] ?? [];
            foreach($cards as $play){
                $pid=$players[(int)($play['seat'] ?? -1)] ?? null;
                if($pid) $last[$pid]=$this->toLongCard((string)($play['card'] ?? ''));
            }
        }
        $phase=$s['phase'] ?? 'bidding';
        if($phase==='round_end') $phase='finished';
        if($phase==='game_over') $phase='finished';
        $out=[
            '_tarneeb_v2'=>$s,
            'phase'=>$phase,
            'game_type'=>'tarneeb',
            'engine_quality'=>'standalone_tarneeb_v132',
            'players'=>$players,
            'dealer'=>$players[(int)($s['dealerSeat'] ?? 3)] ?? null,
            'turn'=>$turn,
            'hands'=>$hands,
            'teams'=>['teamA'=>$teamA,'teamB'=>$teamB],
            'bid'=>$bid,
            'bids'=>$this->bidHistory($s,$players),
            'passed'=>$passed,
            'consecutive_passes'=>0,
            'trump'=>$this->toLongSuit((string)($s['trump'] ?? '')),
            'lead_player'=>!empty($s['trick']) ? ($players[(int)($s['trick'][0]['seat'] ?? 0)] ?? null) : null,
            'trick'=>$trick,
            'last_trick'=>$last,
            'round_tricks'=>[
                'teamA'=>(int)($s['roundTricks'][0] ?? 0),
                'teamB'=>(int)($s['roundTricks'][1] ?? 0),
            ],
            'score'=>[
                'teamA'=>(int)($s['scores'][0] ?? 0),
                'teamB'=>(int)($s['scores'][1] ?? 0),
            ],
            'target'=>(int)($s['rules']['targetScore'] ?? 41),
            'round'=>(int)($s['round'] ?? 1),
            'started_at'=>isset($s['createdAt']) ? date('c',(int)$s['createdAt']) : (function_exists('now')?now()->toIso8601String():date('c')),
            'turn_timeout_seconds'=>max(5,min(10,(int)($s['rules']['turnSeconds'] ?? 7))),
            'messages'=>$extra['messages'] ?? ['طرنيب v132 يعمل بمحرك مستقل مدمج.'],
            'legal_cards'=>[],
            'state_hash'=>$s['security']['stateHash'] ?? null,
        ];
        if($phase==='finished'){
            $out['winner_team']=((int)($s['winnerTeam'] ?? 0)===0?'teamA':'teamB');
            $out['winner']=$out['winner_team'];
            $out['next_round_available']=(string)($s['phase'] ?? '')==='round_end';
        }
        return $out;
    }

    private function toStandalone(array $state): ?array
    {
        $players=array_values(array_map('strval',$state['players'] ?? []));
        if(count($players)<4) return null;

        if(isset($state['_tarneeb_v2']) && is_array($state['_tarneeb_v2'])){
            $standalone=$state['_tarneeb_v2'];
        }else{
            $enginePlayers=[];
            foreach(array_slice($players,0,4) as $player){
                $enginePlayers[]=['id'=>$player,'name'=>$this->shortName($player),'bot'=>str_starts_with($player,'bot:')];
            }
            $target=(int)($state['target'] ?? 41);
            if(!in_array($target,[31,41,61],true)) $target=41;
            $standalone=$this->engine->newGameWithTarget($enginePlayers,$target,1,[
                'turnSeconds'=>max(5,min(10,(int)($state['turn_timeout_seconds'] ?? 7))),
                'targetScore'=>$target,
                'redealOnAllPass'=>true,
                'sortHands'=>true,
                'allowBotForAway'=>true,
                'maxMissedTurnsBeforeAway'=>3,
            ]);
        }

        // Keep the authoritative engine representation synchronized with the
        // public state. This supports rooms created by older Warqna releases
        // and test/admin tools that legitimately edit the public projection.
        $seatByPlayer=[];
        foreach(($standalone['players'] ?? []) as $seat=>$player){
            $seatByPlayer[(string)($player['id'] ?? '')]=(int)$seat;
        }
        if(!$seatByPlayer){
            foreach(array_slice($players,0,4) as $seat=>$player) $seatByPlayer[$player]=$seat;
        }

        $phase=(string)($state['phase'] ?? ($standalone['phase'] ?? 'bidding'));
        $standalone['phase']=match($phase){'finished'=>'round_end',default=>$phase};

        if(isset($state['turn'],$seatByPlayer[(string)$state['turn']])){
            $standalone['currentSeat']=$seatByPlayer[(string)$state['turn']];
        }
        if(isset($state['dealer'],$seatByPlayer[(string)$state['dealer']])){
            $standalone['dealerSeat']=$seatByPlayer[(string)$state['dealer']];
        }

        if(isset($state['hands']) && is_array($state['hands'])){
            foreach($seatByPlayer as $player=>$seat){
                if(array_key_exists($player,$state['hands'])){
                    $standalone['hands'][$seat]=array_values(array_filter(array_map(
                        fn($card)=>$this->toShortCard((string)$card),
                        (array)$state['hands'][$player]
                    )));
                }
            }
        }

        if(array_key_exists('trump',$state)){
            $trump=(string)($state['trump'] ?? '');
            $standalone['trump']=$trump==='' ? null : $this->toShortSuit($trump);
        }

        if(isset($state['trick']) && is_array($state['trick'])){
            $standalone['trick']=[];
            foreach($state['trick'] as $player=>$card){
                if(!isset($seatByPlayer[(string)$player])) continue;
                $seat=$seatByPlayer[(string)$player];
                $standalone['trick'][]=[
                    'seat'=>$seat,
                    'team'=>$standalone['players'][$seat]['team'] ?? ($seat%2),
                    'card'=>$this->toShortCard((string)$card),
                    'at'=>time(),
                ];
            }
        }

        if(isset($state['score']) && is_array($state['score'])){
            $standalone['scores']=[
                0=>(int)($state['score']['teamA'] ?? $state['score'][0] ?? 0),
                1=>(int)($state['score']['teamB'] ?? $state['score'][1] ?? 0),
            ];
        }
        if(isset($state['round_tricks']) && is_array($state['round_tricks'])){
            $standalone['roundTricks']=[
                0=>(int)($state['round_tricks']['teamA'] ?? $state['round_tricks'][0] ?? 0),
                1=>(int)($state['round_tricks']['teamB'] ?? $state['round_tricks'][1] ?? 0),
            ];
        }

        $passed=[0=>false,1=>false,2=>false,3=>false];
        foreach((array)($state['passed'] ?? []) as $player=>$value){
            if($value && isset($seatByPlayer[(string)$player])) $passed[$seatByPlayer[(string)$player]]=true;
        }
        $bid=$state['bid'] ?? null;
        if(is_array($bid) && isset($bid['player'],$seatByPlayer[(string)$bid['player']])){
            $seat=$seatByPlayer[(string)$bid['player']];
            $standalone['bid']=[
                'seat'=>$seat,
                'team'=>$seat%2,
                'amount'=>(int)($bid['value'] ?? $bid['amount'] ?? 0),
                'passed'=>$passed,
                'history'=>$standalone['bid']['history'] ?? [],
            ];
        }elseif(!isset($standalone['bid']) || !is_array($standalone['bid'])){
            $standalone['bid']=['seat'=>null,'team'=>null,'amount'=>0,'passed'=>$passed,'history'=>[]];
        }else{
            $standalone['bid']['passed']=$passed;
        }

        return $standalone;
    }

    private function messagesFromEvents(array $s): array
    {
        $events=array_slice($s['events'] ?? [],-4);
        $out=[];
        foreach($events as $e){
            $type=$e['type'] ?? '';
            $d=$e['data'] ?? [];
            $seat=(int)($d['seat'] ?? $d['winnerSeat'] ?? 0);
            $name=(string)($s['players'][$seat]['name'] ?? ('اللاعب '.($seat+1)));
            if($type==='bid_raise') $out[]=$name.' طلب '.($d['amount'] ?? '').'.';
            elseif($type==='bid_pass') $out[]=$name.' مرّر.';
            elseif($type==='trump_chosen') $out[]=$name.' اختار الطرنيب: '.$this->suitName($this->toLongSuit((string)($d['suit'] ?? ''))).'.';
            elseif($type==='card_played') $out[]=$name.' لعب ورقة.';
            elseif($type==='trick_won') $out[]=$name.' فاز بالأكلة.';
        }
        return $out;
    }

    private function bidHistory(array $s,array $players): array
    {
        $out=[];
        foreach(($s['bid']['history'] ?? []) as $h){
            $seat=(int)($h['seat'] ?? 0);
            $out[]=['player'=>$players[$seat] ?? null,'type'=>$h['action'] ?? 'pass','value'=>$h['amount'] ?? null];
        }
        return $out;
    }

    private function normalizeAction(string $action): string
    {
        return match($action){
            'bid_value'=>'bid',
            'trump','select_trump','choose_hokm'=>'choose_trump',
            'card'=>'play_card',
            default=>$action
        };
    }

    private function buildCardCandidateFromPayload(array $payload): string
    {
        $raw=$payload['card'] ?? $payload['card_id'] ?? $payload['id'] ?? $payload['code'] ?? $payload['value'] ?? '';
        if(is_array($raw)){
            if(isset($raw['id'])) return (string)$raw['id'];
            if(isset($raw['card'])) return (string)$raw['card'];
            if(isset($raw['rank'],$raw['suit'])) return $raw['rank'].'_'.$raw['suit'];
        }
        if(!$raw && isset($payload['rank'],$payload['suit'])) return $payload['rank'].'_'.$payload['suit'];
        return (string)$raw;
    }

    private function canonicalCard(string $card,array $hand): ?string
    {
        return $this->normalizer->canonicalCard($card,$hand);
    }

    private function longCards(array $cards): array { return array_map(fn($c)=>$this->toLongCard((string)$c),$cards); }
    private function toLongCard(string $card): string
    {
        $normalized=$this->normalizer->normalizeCardId($card);
        $parts=explode('_',$normalized,2);
        if(count($parts)!==2) return strtoupper(trim($card));
        return strtoupper($parts[0]).'_'.$this->normalizer->normalizeSuit($parts[1]);
    }
    private function toShortCard(string $card): string
    {
        $normalized=$this->normalizer->normalizeCardId($card);
        $parts=explode('_',$normalized,2);
        if(count($parts)!==2) return strtoupper(trim($card));
        return strtoupper($parts[0]).'_'.$this->toShortSuit($parts[1]);
    }
    private function toLongSuit(string $s): string
    {
        $s=strtoupper(trim($s));
        return match($s){'C'=>'clubs','D'=>'diamonds','S'=>'spades','H'=>'hearts',default=>strtolower($s)};
    }
    private function toShortSuit(string $s): string
    {
        $s=strtolower(trim(str_replace(['️',' ','_','-'],'',$s)));
        return match($s){
            'c','club','clubs','♣','♧','سنك','سباتي','شجرة','تريفل'=>'C',
            'd','diamond','diamonds','♦','♢','ديناري','دينار'=>'D',
            's','spade','spades','♠','♤','بستوني','باص'=>'S',
            'h','heart','hearts','♥','♡','كبة','قلب'=>'H',
            default=>strtoupper($s),
        };
    }
    private function shortName(string $p): string { return str_replace(['user:','bot:'],['لاعب ','بوت '],$p); }
    private function suitName(string $s): string { return ['clubs'=>'♣ سنك','diamonds'=>'♦ ديناري','spades'=>'♠ بستوني','hearts'=>'♥ كبة'][$s] ?? $s; }
}
