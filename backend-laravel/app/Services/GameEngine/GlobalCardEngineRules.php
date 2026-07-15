<?php
namespace App\Services\GameEngine;

require_once __DIR__.'/GlobalEngines/GlobalCardEngineCore.php';
require_once __DIR__.'/GlobalEngines/SyrianTarneebEngine.php';
require_once __DIR__.'/GlobalEngines/Tarneeb400Engine.php';
require_once __DIR__.'/GlobalEngines/HandPartnershipEngine.php';
require_once __DIR__.'/GlobalEngines/SaudiHandEngine.php';
require_once __DIR__.'/GlobalEngines/BanakilEngine.php';
require_once __DIR__.'/GlobalEngines/BanakilClassicEngine.php';
require_once __DIR__.'/GlobalEngines/CompetitiveSolitaireEngine.php';
require_once __DIR__.'/GlobalEngines/TrixEngine.php';
require_once __DIR__.'/GlobalEngines/TrixPartnershipEngine.php';
require_once __DIR__.'/GlobalEngines/TrixComplexEngine.php';
require_once __DIR__.'/GlobalEngines/BalootEngine.php';

class GlobalCardEngineRules implements GameRuleContract
{
    private object $engine;
    private string $key;

    public function __construct(string $key)
    {
        $this->key=$key;
        $this->engine=$this->makeEngine($key);
    }

    public function initialState(array $players, array $options=[]): array
    {
        $cfgPlayers=$this->playerCountFor($this->key, (int)($options['player_count'] ?? count($players)));
        $players=array_values(array_slice($players,0,$cfgPlayers));
        while(count($players)<$cfgPlayers) $players[]='bot:global_'.count($players);
        $enginePlayers=[];
        foreach($players as $i=>$p) $enginePlayers[]=['id'=>(string)$p,'name'=>$this->shortName((string)$p),'bot'=>str_starts_with((string)$p,'bot:')];
        $g=$this->engine->newGame($enginePlayers,[
            'seed'=>random_int(100000,999999999),
            'targetScore'=>(int)($options['target'] ?? $this->defaultTarget($this->key)),
        ]);
        $g=$this->autoBots($g,30);
        return $this->fromGlobal($g,$players,['messages'=>['تم تشغيل '.$this->arabicName($this->key).' بمحرك الألعاب العالمي النهائي final-v1.']]);
    }

    public function validate(array $state, string $playerId, string $action, array $payload): bool
    {
        try{
            $g=$this->globalState($state); if(!$g) return false;
            if(($state['turn'] ?? null)!==$playerId) return false;
            $a=$this->normalizeAction($action,$payload,$state,$playerId);
            $available=$this->engine->availableActions($g,$playerId);
            foreach($available as $x){
                if(($x['type'] ?? '')!==($a['type'] ?? '')) continue;
                if(isset($x['card']) && isset($a['card']) && $x['card']!==$a['card']) continue;
                if(isset($x['amount']) && isset($a['amount']) && (int)$x['amount']!==(int)$a['amount']) continue;
                if(isset($x['suit']) && isset($a['suit']) && $x['suit']!==$a['suit']) continue;
                return true;
            }
            // Structured helpers are validated authoritatively by the engine.
            return in_array(($a['type'] ?? ''), ['organize', 'layoff', 'meld_many', 'pass_trix'], true);
        }catch(\Throwable $e){ return false; }
    }

    public function apply(array $state, string $playerId, string $action, array $payload): array
    {
        try{
            $g=$this->globalState($state); if(!$g){$state['last_error_message']='حالة المحرك العالمي غير موجودة.'; return $state;}
            $a=$this->normalizeAction($action,$payload,$state,$playerId);
            $g=$this->engine->applyAction($g,$playerId,$a);
            $g=$this->autoBots($g,30);
            $next=$this->fromGlobal($g,array_values($state['players'] ?? []));
            $next['messages']=array_values(array_slice(array_merge($state['messages'] ?? [],$this->messagesFromEvents($g)),-50));
            unset($next['last_error'],$next['last_error_message']);
            return $next;
        }catch(\Throwable $e){
            $state['last_error']='global_engine_error';
            $state['last_error_message']=$e->getMessage();
            $state['messages'][]='حركة غير صحيحة: '.$e->getMessage();
            return $state;
        }
    }

    /** @return array<int,array<string,mixed>> */
    public function availableActions(array $state, string $playerId): array
    {
        try {
            $g = $this->globalState($state);
            if (!$g || ($state['turn'] ?? null) !== $playerId) return [];
            $actions = $this->engine->availableActions($g, $playerId);
            return array_values(array_map(function (array $action): array {
                if (isset($action['card'])) $action['card'] = $this->toLongCard((string) $action['card']);
                if (isset($action['cards']) && is_array($action['cards'])) $action['cards'] = $this->longCards($action['cards']);
                if (isset($action['groups']) && is_array($action['groups'])) {
                    $action['groups'] = array_map(fn($group) => $this->longCards((array)$group), $action['groups']);
                }
                if (isset($action['suit'])) $action['suit'] = $this->toLongSuit((string) $action['suit']);
                return $action;
            }, $actions));
        } catch (\Throwable) {
            return [];
        }
    }

    public function onTurnTimeout(array $state): array
    {
        try{
            $g=$this->globalState($state); if(!$g) return $state;
            $pid=(string)($state['turn'] ?? '');
            if($pid){
                try{$g=$this->engine->applyAction($g,$pid,['type'=>'set_away']);}catch(\Throwable $e){}
            }
            $g=$this->autoBots($g,40);
            $next=$this->fromGlobal($g,array_values($state['players'] ?? []));
            $next['messages']=array_values(array_slice(array_merge($state['messages'] ?? [],['⏱️ انتهى الوقت وتم تشغيل حركة تلقائية من المحرك العالمي.'],$this->messagesFromEvents($g)),-50));
            return $next;
        }catch(\Throwable $e){ return $state; }
    }

    private function makeEngine(string $key): object
    {
        return match($key){
            'syrian_tarneeb' => new \SyrianTarneebEngine(),
            'tarneeb_400','400' => new \Tarneeb400Engine(),
            'hand_partner' => new \HandPartnershipEngine(),
            'saudi_hand','hand' => new \SaudiHandEngine(),
            'banakil' => new \BanakilEngine(),
            'pinochle' => new \BanakilClassicEngine(),
            'solitaire_multiplayer' => new \CompetitiveSolitaireEngine(),
            'trix' => new \TrixEngine(),
            'trix_partner' => new \TrixPartnershipEngine(),
            'trix_complex' => new \TrixComplexEngine(),
            'baloot' => new \BalootEngine(),
            default => new \SaudiHandEngine(),
        };
    }

    private function fromGlobal(array $g,array $fallbackPlayers=[],array $extra=[]): array
    {
        $players=array_values(array_map(fn($p)=>(string)$p['id'], $g['players'] ?? []));
        if(!$players) $players=$fallbackPlayers;
        $hands=[];
        foreach(($g['hands'] ?? []) as $pid=>$cards) $hands[$pid]=$this->longCards((array)$cards);
        $turn=$players[(int)($g['currentIndex'] ?? 0)] ?? ($players[0] ?? null);
        $trick=[];
        foreach(($g['trick'] ?? []) as $play) if(isset($play['player'],$play['card'])) $trick[$play['player']]=$this->toLongCard($play['card']);
        $last=[];
        $events=array_reverse($g['events'] ?? []);
        foreach($events as $e){
            if(($e['type'] ?? '')==='trick.won'){
                foreach(($e['data']['cards'] ?? []) as $play) if(isset($play['player'],$play['card'])) $last[$play['player']]=$this->toLongCard($play['card']);
                break;
            }
        }
        $score=$this->score($g,$players);
        $phase=$this->mapPhase((string)($g['phase'] ?? 'playing'));
        if(!empty($g['gameOver'])) $phase='finished';
        $out=[
            '_global_engine'=>$g,
            'phase'=>$phase,
            'engine_phase'=>(string)($g['phase'] ?? $phase),
            'game_type'=>$this->gameType(),
            'game'=>$this->key,
            'engine_quality'=>'global_card_engine_final_v1',
            'players'=>$players,
            'turn'=>$turn,
            'hands'=>$hands,
            'teams'=>$this->teams($players),
            'bid'=>!empty($g['highestBid']) ? ['player'=>$g['highestBid']['player'] ?? null,'value'=>$g['highestBid']['amount'] ?? null,'team'=>$this->teamOf($g,$g['highestBid']['player'] ?? '')] : null,
            'bids'=>$g['bids'] ?? [],
            'trump'=>$this->toLongSuit((string)($g['trump'] ?? '')),
            'contract'=>$g['contract'] ?? null,
            'trick'=>$trick,
            'last_trick'=>$last,
            'round_tricks'=>$this->roundTricks($g,$players),
            'score'=>$score,
            'individual_scores'=>!empty($g['config']['individualScores']) ? ($g['scores'] ?? []) : null,
            'target'=>(int)($g['config']['targetScore'] ?? $this->defaultTarget($this->key)),
            'round'=>(int)($g['round'] ?? 1),
            'turn_timeout_seconds'=>max(5,min(10,(int)($g['config']['turnSeconds'] ?? 7))),
            'messages'=>$extra['messages'] ?? ['محرك عالمي final-v1 مفعّل.'],
            'deck_count'=>count($g['deck'] ?? []),
            'discard'=>$this->longDiscard($g['discard'] ?? []),
            'melds'=>$this->longMelds($g['melds'] ?? []),
            'tableau'=>$g['tableau'] ?? [],
            'trix_board'=>$g['trixBoard'] ?? null,
            'trix_finish_order'=>$g['trixFinishOrder'] ?? [],
            'contracts_used'=>$g['contractsUsed'] ?? [],
            'foundation'=>$g['foundation'] ?? [],
            'legal_cards'=>[],
            'state_hash'=>$g['antiCheat']['lastHash'] ?? null,
            'deal_commitment'=>$g['antiCheat']['dealCommitment'] ?? null,
            'deal_reveal'=>!empty($g['gameOver']) ? ($g['seed'] ?? null) : null,
        ];
        if($phase==='finished'){
            $out['winner']=$g['winner'] ?? null;
            $out['winner_team']=is_int($g['winner'] ?? null) ? (((int)$g['winner'])===0?'teamA':'teamB') : ($g['winner'] ?? null);
        }
        return $out;
    }

    private function normalizeAction(string $action,array $payload,array $state,string $playerId): array
    {
        $a=match($action){
            'bid' => ['type'=>'bid','amount'=>(int)($payload['value'] ?? $payload['amount'] ?? 0)],
            'pass' => ['type'=>'pass'],
            'choose_trump','choose_hokm','select_trump' => ['type'=>'choose_trump','suit'=>$this->toShortSuit((string)($payload['suit'] ?? ''))],
            'choose_contract' => ['type'=>'choose_contract','contract'=>(string)($payload['contract'] ?? 'tricks')],
            'play_card','card' => ['type'=>'play_card','card'=>$this->toShortCard($this->cardPayload($payload),$state['hands'][$playerId] ?? [])],
            'draw_deck','draw' => ['type'=>'draw_deck'],
            'draw_discard' => ['type'=>'draw_discard'],
            'discard' => ['type'=>'discard','card'=>$this->toShortCard($this->cardPayload($payload),$state['hands'][$playerId] ?? [])],
            'meld' => ['type'=>'meld','cards'=>array_map(fn($c)=>$this->toShortCard((string)$c,$state['hands'][$playerId] ?? []),(array)($payload['cards'] ?? []))],
            'meld_many' => ['type'=>'meld_many','groups'=>array_map(
                fn($group)=>array_map(fn($c)=>$this->toShortCard((string)$c,$state['hands'][$playerId] ?? []),(array)$group),
                (array)($payload['groups'] ?? [])
            )],
            'pass_trix' => ['type'=>'pass_trix'],
            'layoff','attach' => [
                'type'=>'layoff',
                'target_player'=>(string)($payload['target_player'] ?? $payload['targetPlayer'] ?? $playerId),
                'meld_index'=>(int)($payload['meld_index'] ?? $payload['meldIndex'] ?? 0),
                'cards'=>array_map(fn($c)=>$this->toShortCard((string)$c,$state['hands'][$playerId] ?? []),(array)($payload['cards'] ?? [])),
            ],
            'organize' => ['type'=>'organize','strategy'=>'smart'],
            'draw_stock' => ['type'=>'draw_stock'],
            'move_to_foundation' => ['type'=>'move_to_foundation','card'=>$this->toShortCard($this->cardPayload($payload),$state['hands'][$playerId] ?? [])],
            default => ['type'=>$action],
        };
        return $a;
    }

    private function autoBots(array $g,int $max): array
    {
        for($i=0;$i<$max;$i++){
            if(!empty($g['gameOver'])) break;
            $cur=$g['players'][(int)($g['currentIndex'] ?? 0)] ?? null;
            if(!$cur || (empty($cur['bot']) && empty($cur['away']))) break;
            $g=$this->engine->botMove($g);
        }
        return $g;
    }

    private function globalState(array $state): ?array { return isset($state['_global_engine']) && is_array($state['_global_engine']) ? $state['_global_engine'] : null; }
    private function playerCountFor(string $key, int $requested): int
    {
        return match($key) {
            'hand','saudi_hand' => max(2, min(5, $requested)),
            'banakil','pinochle' => $requested <= 2 ? 2 : 4,
            'solitaire_multiplayer' => max(2, min(4, $requested)),
            default => 4,
        };
    }
    private function defaultTarget(string $key): int { return match($key){'baloot'=>152,'tarneeb_400'=>41,'banakil','pinochle'=>222,default=>101}; }
    private function gameType(): string { return match($this->key){'trix','trix_partner','trix_complex'=>'trix','hand','hand_partner','saudi_hand','banakil','pinochle','solitaire_multiplayer'=>'hand','baloot'=>'baloot','syrian_tarneeb','tarneeb_400'=>'tarneeb',default=>$this->key}; }
    private function mapPhase(string $p): string { return match($p){'contract'=>'choose_contract','trix_playing'=>'playing','draw','discard'=>'playing',default=>$p}; }
    private function cardPayload(array $p): string { $v=$p['card'] ?? $p['card_id'] ?? $p['id'] ?? $p['code'] ?? ''; return is_array($v) ? (string)($v['id'] ?? $v['card'] ?? '') : (string)$v; }

    private function toShortCard(string $card,array $hand=[]): string
    {
        if(!$card && $hand) return $this->toShortCard((string)$hand[0],[]);
        $c=trim($card);
        $c=str_replace(['clubs','♣','سنك','شجرة'],'C',$c);
        $c=str_replace(['diamonds','♦','ديناري'],'D',$c);
        $c=str_replace(['spades','♠','بستوني'],'S',$c);
        $c=str_replace(['hearts','♥','كبة'],'H',$c);
        $c=str_replace(['-',' '],['_','_'],$c);
        $p=array_values(array_filter(explode('_',$c),fn($x)=>$x!==''));
        $out=count($p)>=2 ? (in_array(strtoupper($p[0]),['C','D','S','H'],true) ? strtoupper(end($p)).'_'.strtoupper($p[0]) : strtoupper($p[0]).'_'.$this->toShortSuit((string)end($p))) : strtoupper($c);
        foreach($hand as $h) if($this->toShortCard((string)$h,[])===$out) return $out;
        return $out;
    }
    private function toLongCard(string $c): string { $p=explode('_',strtoupper(trim($c))); return count($p)>=2 ? $p[0].'_'.$this->toLongSuit($p[1]) : $c; }
    private function longCards(array $cards): array { return array_map(fn($c)=>$this->toLongCard((string)$c),$cards); }
    private function toShortSuit(string $s): string { $s=strtolower(trim(str_replace(['️',' ','_','-'],'',$s))); return match($s){'c','club','clubs','♣','سنك','شجرة'=>'C','d','diamond','diamonds','♦','ديناري'=>'D','s','spade','spades','♠','بستوني'=>'S','h','heart','hearts','♥','كبة'=>'H',default=>strtoupper($s)}; }
    private function toLongSuit(string $s): string { $s=strtoupper(trim($s)); return match($s){'C'=>'clubs','D'=>'diamonds','S'=>'spades','H'=>'hearts',default=>strtolower($s)}; }
    private function teams(array $players): array { return ['teamA'=>array_values(array_filter([$players[0]??null,$players[2]??null])),'teamB'=>array_values(array_filter([$players[1]??null,$players[3]??null]))]; }
    private function teamOf(array $g,string $pid): string { foreach($g['players'] ?? [] as $p) if(($p['id'] ?? '')===$pid) return ((int)($p['team'] ?? 0))===0?'teamA':'teamB'; return 'teamA'; }
    private function score(array $g,array $players): array
    {
        $scores=$g['scores'] ?? [];
        if (!empty($g['config']['individualScores'])) {
            return [
                'teamA'=>(float)($scores[$players[0] ?? ''] ?? 0)+(float)($scores[$players[2] ?? ''] ?? 0),
                'teamB'=>(float)($scores[$players[1] ?? ''] ?? 0)+(float)($scores[$players[3] ?? ''] ?? 0),
            ];
        }
        if (array_key_exists(0,$scores) || array_key_exists(1,$scores)) {
            return ['teamA'=>$scores[0] ?? 0,'teamB'=>$scores[1] ?? 0];
        }
        return ['teamA'=>$scores[$players[0] ?? ''] ?? 0,'teamB'=>$scores[$players[1] ?? ''] ?? 0];
    }

    private function roundTricks(array $g,array $players): array
    {
        $tricks=$g['tricksWon'] ?? [];
        if (!empty($g['config']['individualScores'])) {
            return [
                'teamA'=>(int)($tricks[$players[0] ?? ''] ?? 0)+(int)($tricks[$players[2] ?? ''] ?? 0),
                'teamB'=>(int)($tricks[$players[1] ?? ''] ?? 0)+(int)($tricks[$players[3] ?? ''] ?? 0),
            ];
        }
        return ['teamA'=>(int)($tricks[0] ?? 0),'teamB'=>(int)($tricks[1] ?? 0)];
    }
    private function longDiscard(array $discard): array
    {
        return array_values(array_map(function ($item) {
            if (is_array($item)) {
                if (isset($item['card'])) $item['card'] = $this->toLongCard((string) $item['card']);
                return $item;
            }
            return $this->toLongCard((string) $item);
        }, $discard));
    }
    private function longMelds(array $melds): array { foreach($melds as $pid=>$list) foreach($list as $i=>$m) if(isset($m['cards'])) $melds[$pid][$i]['cards']=$this->longCards((array)$m['cards']); return $melds; }
    private function messagesFromEvents(array $g): array
    {
        $names=[];foreach($g['players'] ?? [] as $player)$names[(string)($player['id'] ?? '')]=(string)($player['name'] ?? $player['id'] ?? 'لاعب');
        $out=[];foreach(array_slice($g['events'] ?? [],-4) as $event){
            $type=(string)($event['type'] ?? 'move');$data=$event['data'] ?? [];
            $playerId=(string)($data['playerId'] ?? $data['winner'] ?? '');$name=$names[$playerId] ?? $this->shortName($playerId ?: 'لاعب');
            $out[]=match($type){
                'bid.pass'=>$name.' مرّر.',
                'bid.made'=>$name.' طلب '.($data['amount'] ?? '').'.',
                'trump.chosen'=>$name.' اختار الحكم.',
                'contract.chosen'=>$name.' اختار العقد '.($data['contract'] ?? '').'.',
                'card.played'=>$name.' لعب ورقة.',
                'trick.won'=>$name.' فاز بالأكلة.',
                'rummy.draw_deck'=>$name.' سحب من الرزمة.',
                'rummy.draw_discard'=>$name.' سحب الورقة المكشوفة.',
                'rummy.discard'=>$name.' رمى ورقة.',
                'rummy.meld'=>$name.' أنزل مجموعة قانونية.',
                'rummy.meld_many'=>$name.' أنزل عدة مجموعات قانونية.',
                'trix.card_played'=>$name.' ركّب ورقة في عقد تركس.',
                'trix.pass'=>$name.' مرّر لعدم وجود ورقة قابلة للتركيب.',
                'rummy.layoff'=>$name.' ركّب أوراقًا على مجموعة موجودة.',
                default=>'• '.str_replace(['.','_'],' ',$type),
            };
        }return $out;
    }
    private function arabicName(string $key): string { return ['syrian_tarneeb'=>'الطرنيب السوري','tarneeb_400'=>'طرنيب 400','hand'=>'الهاند','hand_partner'=>'هاند شراكة','saudi_hand'=>'الهاند السعودي','banakil'=>'بناكل','pinochle'=>'بناكل','solitaire_multiplayer'=>'السوليتير التنافسي','trix'=>'تركس','trix_partner'=>'تركس شراكة','trix_complex'=>'تركس كمبلكس','baloot'=>'بلوت'][$key] ?? $key; }
    private function shortName(string $p): string { return str_replace(['user:','bot:'],['لاعب ','بوت '],$p); }
}
